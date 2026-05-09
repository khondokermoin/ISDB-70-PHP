import { createFileRoute } from "@tanstack/react-router";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth-context";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter } from "@/components/ui/dialog";
import { Plus, Wifi } from "lucide-react";
import { useState } from "react";
import { toast } from "sonner";

export const Route = createFileRoute("/app/packages")({
  component: PackagesPage,
});

function PackagesPage() {
  const { isAdmin, user } = useAuth();
  const qc = useQueryClient();

  const { data: packages, isLoading } = useQuery({
    queryKey: ["packages"],
    queryFn: async () => {
      const { data, error } = await supabase.from("packages").select("*").order("price");
      if (error) throw error;
      return data;
    },
  });

  const subscribe = useMutation({
    mutationFn: async (pkg: any) => {
      if (!user) throw new Error("Not signed in");
      const start = new Date();
      const end = new Date();
      end.setDate(end.getDate() + (pkg.duration_days ?? 30));
      const { error: subErr, data: sub } = await supabase
        .from("subscriptions")
        .insert({
          user_id: user.id,
          package_id: pkg.package_id,
          start_date: start.toISOString().slice(0, 10),
          end_date: end.toISOString().slice(0, 10),
          status: "active",
        })
        .select()
        .single();
      if (subErr) throw subErr;

      const invNum = `INV-${Date.now()}`;
      const due = new Date();
      due.setDate(due.getDate() + 7);
      const { error: invErr } = await supabase.from("invoices").insert({
        user_id: user.id,
        subscription_id: sub.subscription_id,
        invoice_number: invNum,
        period_start: start.toISOString().slice(0, 10),
        period_end: end.toISOString().slice(0, 10),
        amount: pkg.price,
        due_date: due.toISOString().slice(0, 10),
      });
      if (invErr) throw invErr;
    },
    onSuccess: () => {
      toast.success("Subscribed! Invoice created.");
      qc.invalidateQueries({ queryKey: ["subscriptions"] });
      qc.invalidateQueries({ queryKey: ["invoices"] });
    },
    onError: (e: any) => toast.error(e.message),
  });

  return (
    <div className="space-y-6">
      <div className="flex items-end justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Internet packages</h1>
          <p className="text-muted-foreground">Choose the speed that fits your home or office.</p>
        </div>
        {isAdmin && <NewPackageDialog onSaved={() => qc.invalidateQueries({ queryKey: ["packages"] })} />}
      </div>

      {isLoading ? (
        <p className="text-muted-foreground">Loading…</p>
      ) : (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {packages?.map((p) => (
            <Card key={p.package_id} className="shadow-soft hover:shadow-elevated transition relative overflow-hidden">
              <div className="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-gradient-brand opacity-10" />
              <CardHeader>
                <div className="flex items-center justify-between">
                  <CardTitle>{p.name}</CardTitle>
                  <Badge variant={p.status === "active" ? "default" : "secondary"}>{p.status}</Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <div className="flex items-baseline gap-1">
                    <Wifi className="h-5 w-5 text-primary" />
                    <span className="text-3xl font-bold">{p.speed_mbps}</span>
                    <span className="text-muted-foreground">Mbps</span>
                  </div>
                  <p className="text-sm text-muted-foreground mt-1">
                    {p.quota_gb ? `${p.quota_gb} GB quota` : "Unlimited"} · {p.duration_days} days
                  </p>
                </div>
                <div className="flex items-end justify-between pt-2 border-t">
                  <div>
                    <p className="text-xs text-muted-foreground">Price</p>
                    <p className="text-xl font-semibold">Rp {Number(p.price).toLocaleString("id-ID")}</p>
                  </div>
                  <Button onClick={() => subscribe.mutate(p)} disabled={subscribe.isPending}>
                    Subscribe
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}

function NewPackageDialog({ onSaved }: { onSaved: () => void }) {
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({ name: "", speed_mbps: 50, quota_gb: 300, price: 299000, duration_days: 30 });

  const save = async () => {
    const { error } = await supabase.from("packages").insert(form);
    if (error) return toast.error(error.message);
    toast.success("Package created");
    setOpen(false);
    onSaved();
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button><Plus className="h-4 w-4 mr-1" /> New package</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader><DialogTitle>New package</DialogTitle></DialogHeader>
        <div className="grid gap-3">
          <div><Label>Name</Label><Input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} /></div>
          <div className="grid grid-cols-2 gap-3">
            <div><Label>Speed (Mbps)</Label><Input type="number" value={form.speed_mbps} onChange={(e) => setForm({ ...form, speed_mbps: +e.target.value })} /></div>
            <div><Label>Quota (GB)</Label><Input type="number" value={form.quota_gb} onChange={(e) => setForm({ ...form, quota_gb: +e.target.value })} /></div>
          </div>
          <div className="grid grid-cols-2 gap-3">
            <div><Label>Price</Label><Input type="number" value={form.price} onChange={(e) => setForm({ ...form, price: +e.target.value })} /></div>
            <div><Label>Duration (days)</Label><Input type="number" value={form.duration_days} onChange={(e) => setForm({ ...form, duration_days: +e.target.value })} /></div>
          </div>
        </div>
        <DialogFooter><Button onClick={save}>Save</Button></DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
