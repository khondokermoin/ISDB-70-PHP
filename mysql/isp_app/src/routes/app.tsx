import { createFileRoute, Link, Outlet, useNavigate, useRouterState } from "@tanstack/react-router";
import { useEffect } from "react";
import { useAuth } from "@/lib/auth-context";
import { Button } from "@/components/ui/button";
import { Wifi, LayoutDashboard, Package, FileText, LifeBuoy, User as UserIcon, LogOut, Users, Activity } from "lucide-react";
import { cn } from "@/lib/utils";

export const Route = createFileRoute("/app")({
  component: AppLayout,
});

function AppLayout() {
  const { user, loading, isAdmin, isStaff, signOut } = useAuth();
  const navigate = useNavigate();
  const pathname = useRouterState({ select: (s) => s.location.pathname });

  useEffect(() => {
    if (!loading && !user) navigate({ to: "/auth" });
  }, [loading, user, navigate]);

  if (loading || !user) {
    return <div className="min-h-screen grid place-items-center text-muted-foreground">Loading…</div>;
  }

  const nav = [
    { to: "/app/dashboard", label: "Dashboard", icon: LayoutDashboard },
    { to: "/app/packages", label: "Packages", icon: Package },
    { to: "/app/subscriptions", label: "Subscriptions", icon: Activity },
    { to: "/app/invoices", label: "Invoices", icon: FileText },
    { to: "/app/tickets", label: "Support", icon: LifeBuoy },
    ...(isStaff ? [{ to: "/app/customers", label: "Customers", icon: Users }] : []),
    { to: "/app/profile", label: "Profile", icon: UserIcon },
  ];

  return (
    <div className="min-h-screen bg-background flex">
      <aside className="hidden md:flex w-64 flex-col bg-sidebar text-sidebar-foreground border-r border-sidebar-border">
        <Link to="/app/dashboard" className="flex items-center gap-2 px-6 h-16 font-semibold border-b border-sidebar-border">
          <span className="grid h-8 w-8 place-items-center rounded-lg bg-gradient-brand text-primary-foreground">
            <Wifi className="h-4 w-4" />
          </span>
          NetFlow
        </Link>
        <nav className="flex-1 p-3 space-y-1">
          {nav.map((n) => {
            const active = pathname === n.to || pathname.startsWith(n.to + "/");
            return (
              <Link
                key={n.to}
                to={n.to}
                className={cn(
                  "flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition",
                  active ? "bg-sidebar-accent text-white" : "hover:bg-white/5"
                )}
              >
                <n.icon className="h-4 w-4" />
                {n.label}
              </Link>
            );
          })}
        </nav>
        <div className="p-3 border-t border-sidebar-border">
          <div className="px-3 py-2 text-xs text-white/60 truncate">{user.email}</div>
          {isAdmin && <div className="px-3 pb-2 text-[10px] uppercase tracking-wider text-primary-glow">Admin</div>}
          <Button
            variant="ghost"
            className="w-full justify-start gap-3 text-white/80 hover:text-white hover:bg-white/5"
            onClick={async () => { await signOut(); navigate({ to: "/" }); }}
          >
            <LogOut className="h-4 w-4" /> Sign out
          </Button>
        </div>
      </aside>

      <main className="flex-1 min-w-0">
        <header className="md:hidden h-14 border-b flex items-center px-4 justify-between">
          <Link to="/app/dashboard" className="font-semibold flex items-center gap-2">
            <Wifi className="h-4 w-4 text-primary" /> NetFlow
          </Link>
          <Button variant="ghost" size="sm" onClick={async () => { await signOut(); navigate({ to: "/" }); }}>
            <LogOut className="h-4 w-4" />
          </Button>
        </header>
        <div className="p-6 md:p-8 max-w-7xl mx-auto">
          <Outlet />
        </div>
      </main>
    </div>
  );
}
