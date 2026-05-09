import { createFileRoute } from "@tanstack/react-router";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth-context";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { toast } from "sonner";

export const Route = createFileRoute("/app/invoices")({
  component: InvoicesPage,
});

function InvoicesPage() {
  const { user } = useAuth();
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ["invoices"],
    queryFn: async () => {
      const { data, error } = await supabase
        .from("invoices")
        .select("*")
        .order("created_at", { ascending: false });
      if (error) throw error;
      return data;
    },
  });

  const pay = useMutation({
    mutationFn: async (inv: any) => {
      if (!user) throw new Error("Not signed in");
      const { error: payErr } = await supabase.from("payments").insert({
        invoice_id: inv.invoice_id,
        user_id: user.id,
        amount: inv.amount,
        method: "card",
        transaction_ref: `TX-${Date.now()}`,
      });
      if (payErr) throw payErr;
      const { error: updErr } = await supabase
        .from("invoices")
        .update({ status: "paid" })
        .eq("invoice_id", inv.invoice_id);
      if (updErr) throw updErr;
    },
    onSuccess: () => {
      toast.success("Payment recorded");
      qc.invalidateQueries({ queryKey: ["invoices"] });
    },
    onError: (e: any) => toast.error(e.message),
  });

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Invoices</h1>
        <p className="text-muted-foreground">Pay due bills and review billing history.</p>
      </div>
      <Card>
        <CardHeader><CardTitle>All invoices</CardTitle></CardHeader>
        <CardContent>
          {isLoading ? <p className="text-muted-foreground">Loading…</p> : !data?.length ? (
            <p className="text-sm text-muted-foreground py-6 text-center">No invoices.</p>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Number</TableHead>
                  <TableHead>Period</TableHead>
                  <TableHead>Due</TableHead>
                  <TableHead>Amount</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead className="text-right">Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data.map((i) => (
                  <TableRow key={i.invoice_id}>
                    <TableCell className="font-medium">{i.invoice_number}</TableCell>
                    <TableCell className="text-muted-foreground">
                      {new Date(i.period_start).toLocaleDateString()} → {new Date(i.period_end).toLocaleDateString()}
                    </TableCell>
                    <TableCell>{new Date(i.due_date).toLocaleDateString()}</TableCell>
                    <TableCell>Rp {Number(i.amount).toLocaleString("id-ID")}</TableCell>
                    <TableCell>
                      <Badge variant={i.status === "paid" ? "default" : i.status === "unpaid" ? "secondary" : "outline"}>
                        {i.status}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right">
                      {i.status !== "paid" && (
                        <Button size="sm" onClick={() => pay.mutate(i)} disabled={pay.isPending}>Pay now</Button>
                      )}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
