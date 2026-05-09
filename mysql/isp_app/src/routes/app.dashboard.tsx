import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth-context";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Activity, FileText, LifeBuoy, Package as PackageIcon, ArrowRight } from "lucide-react";

export const Route = createFileRoute("/app/dashboard")({
  component: Dashboard,
});

function Dashboard() {
  const { user, isAdmin, isStaff } = useAuth();

  const { data: stats } = useQuery({
    queryKey: ["dashboard-stats", user?.id, isAdmin, isStaff],
    queryFn: async () => {
      const subsQ = supabase.from("subscriptions").select("status", { count: "exact", head: true });
      const invQ = supabase.from("invoices").select("status,amount");
      const tickQ = supabase.from("tickets").select("status", { count: "exact", head: true });
      const pkgQ = supabase.from("packages").select("package_id", { count: "exact", head: true });

      const [subs, inv, tick, pkg] = await Promise.all([subsQ, invQ, tickQ, pkgQ]);
      const unpaid = (inv.data ?? []).filter((i) => i.status === "unpaid");
      const totalDue = unpaid.reduce((s, i) => s + Number(i.amount), 0);
      return {
        subscriptions: subs.count ?? 0,
        tickets: tick.count ?? 0,
        packages: pkg.count ?? 0,
        unpaidCount: unpaid.length,
        totalDue,
      };
    },
  });

  const { data: recentInvoices } = useQuery({
    queryKey: ["recent-invoices", user?.id],
    queryFn: async () => {
      const { data } = await supabase
        .from("invoices")
        .select("invoice_id, invoice_number, amount, status, due_date")
        .order("created_at", { ascending: false })
        .limit(5);
      return data ?? [];
    },
  });

  const cards = [
    { label: isStaff ? "Active subscriptions" : "My subscriptions", value: stats?.subscriptions ?? "—", icon: Activity, tint: "bg-accent text-accent-foreground" },
    { label: "Unpaid invoices", value: stats?.unpaidCount ?? "—", icon: FileText, tint: "bg-warning/15 text-warning-foreground" },
    { label: "Open tickets", value: stats?.tickets ?? "—", icon: LifeBuoy, tint: "bg-primary/10 text-primary" },
    { label: "Packages", value: stats?.packages ?? "—", icon: PackageIcon, tint: "bg-success/15 text-success-foreground" },
  ];

  return (
    <div className="space-y-8">
      <div className="flex items-end justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
          <p className="text-muted-foreground">Welcome back{user?.email ? `, ${user.email}` : ""}.</p>
        </div>
        {isAdmin && <Badge variant="secondary">Admin</Badge>}
      </div>

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {cards.map((c) => (
          <Card key={c.label} className="shadow-soft">
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{c.label}</p>
                  <p className="mt-2 text-3xl font-bold">{c.value}</p>
                </div>
                <div className={`grid h-10 w-10 place-items-center rounded-lg ${c.tint}`}>
                  <c.icon className="h-5 w-5" />
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <CardTitle>Recent invoices</CardTitle>
          <Link to="/app/invoices"><Button variant="ghost" size="sm">View all <ArrowRight className="ml-1 h-3 w-3" /></Button></Link>
        </CardHeader>
        <CardContent>
          {!recentInvoices?.length ? (
            <p className="text-sm text-muted-foreground py-6 text-center">No invoices yet.</p>
          ) : (
            <div className="divide-y">
              {recentInvoices.map((i) => (
                <div key={i.invoice_id} className="flex items-center justify-between py-3">
                  <div>
                    <p className="font-medium">{i.invoice_number}</p>
                    <p className="text-xs text-muted-foreground">Due {new Date(i.due_date).toLocaleDateString()}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="font-medium">Rp {Number(i.amount).toLocaleString("id-ID")}</span>
                    <Badge variant={i.status === "paid" ? "default" : "secondary"}>{i.status}</Badge>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
