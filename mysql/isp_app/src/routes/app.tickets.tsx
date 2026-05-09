import { createFileRoute } from "@tanstack/react-router";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth-context";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter } from "@/components/ui/dialog";
import { Plus, MessageSquare } from "lucide-react";
import { useState } from "react";
import { toast } from "sonner";

export const Route = createFileRoute("/app/tickets")({
  component: TicketsPage,
});

function TicketsPage() {
  const { user, isStaff } = useAuth();
  const qc = useQueryClient();
  const [openTicket, setOpenTicket] = useState<any>(null);

  const { data: tickets, isLoading } = useQuery({
    queryKey: ["tickets"],
    queryFn: async () => {
      const { data, error } = await supabase
        .from("tickets")
        .select("*")
        .order("created_at", { ascending: false });
      if (error) throw error;
      return data;
    },
  });

  return (
    <div className="space-y-6">
      <div className="flex items-end justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Support tickets</h1>
          <p className="text-muted-foreground">Open a ticket and track replies.</p>
        </div>
        <NewTicketDialog onSaved={() => qc.invalidateQueries({ queryKey: ["tickets"] })} userId={user!.id} />
      </div>

      <Card>
        <CardHeader><CardTitle>Tickets</CardTitle></CardHeader>
        <CardContent className="p-0">
          {isLoading ? <p className="p-6 text-muted-foreground">Loading…</p> : !tickets?.length ? (
            <p className="p-6 text-sm text-muted-foreground text-center">No tickets yet.</p>
          ) : (
            <div className="divide-y">
              {tickets.map((t) => (
                <button
                  key={t.ticket_id}
                  onClick={() => setOpenTicket(t)}
                  className="w-full flex items-center justify-between p-4 hover:bg-muted/40 text-left transition"
                >
                  <div>
                    <p className="font-medium">{t.subject}</p>
                    <p className="text-xs text-muted-foreground">{new Date(t.created_at!).toLocaleString()}</p>
                  </div>
                  <div className="flex items-center gap-2">
                    <Badge variant="outline">{t.priority}</Badge>
                    <Badge variant={t.status === "open" ? "default" : "secondary"}>{t.status}</Badge>
                  </div>
                </button>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {openTicket && (
        <TicketDetail ticket={openTicket} onClose={() => setOpenTicket(null)} canManage={isStaff} userId={user!.id} />
      )}
    </div>
  );
}

function NewTicketDialog({ onSaved, userId }: { onSaved: () => void; userId: string }) {
  const [open, setOpen] = useState(false);
  const [subject, setSubject] = useState("");
  const [priority, setPriority] = useState("normal");
  const [message, setMessage] = useState("");

  const save = async () => {
    const { data: t, error } = await supabase
      .from("tickets")
      .insert({ user_id: userId, subject, priority })
      .select()
      .single();
    if (error) return toast.error(error.message);
    if (message.trim()) {
      await supabase.from("ticket_replies").insert({ ticket_id: t.ticket_id, user_id: userId, message });
    }
    toast.success("Ticket created");
    setOpen(false);
    setSubject(""); setMessage(""); setPriority("normal");
    onSaved();
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button><Plus className="h-4 w-4 mr-1" /> New ticket</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader><DialogTitle>New support ticket</DialogTitle></DialogHeader>
        <div className="grid gap-3">
          <div><Label>Subject</Label><Input value={subject} onChange={(e) => setSubject(e.target.value)} /></div>
          <div>
            <Label>Priority</Label>
            <Select value={priority} onValueChange={setPriority}>
              <SelectTrigger><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="low">Low</SelectItem>
                <SelectItem value="normal">Normal</SelectItem>
                <SelectItem value="high">High</SelectItem>
                <SelectItem value="urgent">Urgent</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div><Label>Message</Label><Textarea rows={4} value={message} onChange={(e) => setMessage(e.target.value)} /></div>
        </div>
        <DialogFooter><Button onClick={save} disabled={!subject}>Create</Button></DialogFooter>
      </DialogContent>
    </Dialog>
  );
}

function TicketDetail({ ticket, onClose, canManage, userId }: { ticket: any; onClose: () => void; canManage: boolean; userId: string }) {
  const qc = useQueryClient();
  const [reply, setReply] = useState("");

  const { data: replies } = useQuery({
    queryKey: ["ticket-replies", ticket.ticket_id],
    queryFn: async () => {
      const { data } = await supabase
        .from("ticket_replies")
        .select("*")
        .eq("ticket_id", ticket.ticket_id)
        .order("replied_at");
      return data ?? [];
    },
  });

  const post = useMutation({
    mutationFn: async () => {
      const { error } = await supabase.from("ticket_replies").insert({
        ticket_id: ticket.ticket_id,
        user_id: userId,
        message: reply,
      });
      if (error) throw error;
    },
    onSuccess: () => {
      setReply("");
      qc.invalidateQueries({ queryKey: ["ticket-replies", ticket.ticket_id] });
    },
    onError: (e: any) => toast.error(e.message),
  });

  const updateStatus = async (status: string) => {
    const { error } = await supabase.from("tickets").update({ status }).eq("ticket_id", ticket.ticket_id);
    if (error) return toast.error(error.message);
    toast.success("Updated");
    qc.invalidateQueries({ queryKey: ["tickets"] });
  };

  return (
    <Dialog open onOpenChange={onClose}>
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2"><MessageSquare className="h-4 w-4" /> {ticket.subject}</DialogTitle>
        </DialogHeader>
        <div className="space-y-4">
          <div className="flex items-center gap-2">
            <Badge variant="outline">{ticket.priority}</Badge>
            <Badge>{ticket.status}</Badge>
            {canManage && (
              <Select onValueChange={updateStatus} defaultValue={ticket.status}>
                <SelectTrigger className="w-40 ml-auto"><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="open">Open</SelectItem>
                  <SelectItem value="in_progress">In progress</SelectItem>
                  <SelectItem value="resolved">Resolved</SelectItem>
                  <SelectItem value="closed">Closed</SelectItem>
                </SelectContent>
              </Select>
            )}
          </div>
          <div className="max-h-72 overflow-auto space-y-3 border rounded-lg p-3 bg-muted/30">
            {!replies?.length ? (
              <p className="text-sm text-muted-foreground text-center py-6">No messages yet.</p>
            ) : replies.map((r) => (
              <div key={r.reply_id} className="rounded-md bg-card p-3 shadow-soft">
                <p className="text-sm">{r.message}</p>
                <p className="text-xs text-muted-foreground mt-1">{new Date(r.replied_at!).toLocaleString()}</p>
              </div>
            ))}
          </div>
          <div className="space-y-2">
            <Textarea rows={3} value={reply} onChange={(e) => setReply(e.target.value)} placeholder="Write a reply…" />
            <div className="flex justify-end">
              <Button onClick={() => post.mutate()} disabled={!reply.trim() || post.isPending}>Send reply</Button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
