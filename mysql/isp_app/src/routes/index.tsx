import { createFileRoute, Link } from "@tanstack/react-router";
import { Button } from "@/components/ui/button";
import { Wifi, Zap, ShieldCheck, HeadphonesIcon, Gauge, CreditCard } from "lucide-react";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title: "NetFlow ISP — Lightning-fast internet, simply managed" },
      { name: "description", content: "Browse packages, subscribe online, pay invoices, and get support — all in one ISP portal." },
    ],
  }),
  component: Landing,
});

function Landing() {
  return (
    <div className="min-h-screen bg-background">
      <header className="sticky top-0 z-30 backdrop-blur bg-background/70 border-b">
        <div className="container mx-auto flex h-16 items-center justify-between px-4">
          <Link to="/" className="flex items-center gap-2 font-semibold">
            <span className="grid h-8 w-8 place-items-center rounded-lg bg-gradient-brand text-primary-foreground">
              <Wifi className="h-4 w-4" />
            </span>
            NetFlow
          </Link>
          <nav className="flex items-center gap-2">
            <Link to="/auth"><Button variant="ghost">Sign in</Button></Link>
            <Link to="/auth"><Button>Get started</Button></Link>
          </nav>
        </div>
      </header>

      <section className="relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-hero opacity-10" />
        <div className="container mx-auto px-4 py-24 md:py-32 relative">
          <div className="max-w-3xl">
            <span className="inline-flex items-center gap-2 rounded-full border bg-card px-3 py-1 text-xs font-medium text-muted-foreground shadow-soft">
              <span className="h-2 w-2 rounded-full bg-success animate-pulse" /> Network 99.98% uptime
            </span>
            <h1 className="mt-6 text-5xl md:text-6xl font-bold tracking-tight leading-[1.05]">
              Internet, billing, and support —{" "}
              <span className="bg-gradient-brand bg-clip-text text-transparent">all in one portal</span>
            </h1>
            <p className="mt-6 text-lg text-muted-foreground max-w-2xl">
              NetFlow is the modern way to run an ISP. Manage packages, subscriptions, invoices,
              and customer tickets from a single beautiful dashboard.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Link to="/auth"><Button size="lg">Open dashboard</Button></Link>
              <Link to="/auth"><Button size="lg" variant="outline">Create account</Button></Link>
            </div>
          </div>
        </div>
      </section>

      <section className="container mx-auto px-4 py-20">
        <div className="grid gap-6 md:grid-cols-3">
          {[
            { icon: Gauge, title: "Real-time usage", desc: "Track data consumption and network status in one glance." },
            { icon: CreditCard, title: "Smart billing", desc: "Auto-generated invoices, multiple payment methods, due reminders." },
            { icon: HeadphonesIcon, title: "Built-in support", desc: "Customers open tickets; staff resolve them with full thread history." },
            { icon: Zap, title: "Speed-tiered packages", desc: "From 20 Mbps starter to 300 Mbps ultra fiber." },
            { icon: ShieldCheck, title: "Secure by default", desc: "Role-based access keeps customer data isolated." },
            { icon: Wifi, title: "Customer self-service", desc: "Subscribe, pay, and contact support without a phone call." },
          ].map((f) => (
            <div key={f.title} className="rounded-2xl border bg-card p-6 shadow-soft transition hover:shadow-elevated">
              <div className="grid h-10 w-10 place-items-center rounded-lg bg-accent text-accent-foreground">
                <f.icon className="h-5 w-5" />
              </div>
              <h3 className="mt-4 font-semibold">{f.title}</h3>
              <p className="mt-1 text-sm text-muted-foreground">{f.desc}</p>
            </div>
          ))}
        </div>
      </section>

      <footer className="border-t py-8 text-center text-sm text-muted-foreground">
        © {new Date().getFullYear()} NetFlow ISP
      </footer>
    </div>
  );
}
