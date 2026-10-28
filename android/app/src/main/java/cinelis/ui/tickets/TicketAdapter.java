package cinelis.ui.tickets;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

import cinelis.R;
import cinelis.model.CinemaSession;
import cinelis.model.Ticket;

public final class TicketAdapter extends RecyclerView.Adapter<TicketAdapter.Holder> {
    public interface Listener { void onTicketSelected(Ticket ticket); }
    private final List<Ticket> tickets = new ArrayList<>();
    private final Listener listener;
    public TicketAdapter(Listener listener) { this.listener = listener; }
    public void submit(List<Ticket> values) { tickets.clear(); tickets.addAll(values); notifyDataSetChanged(); }
    @NonNull @Override public Holder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) { return new Holder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_ticket, parent, false)); }
    @Override public void onBindViewHolder(@NonNull Holder holder, int position) {
        Ticket ticket = tickets.get(position);
        CinemaSession session = ticket.getSession();
        holder.title.setText(session.getMovie() == null ? "Bilhete CineLis" : session.getMovie().getTitle());
        String startsAt = session.getStartsAt();
        holder.meta.setText(startsAt.substring(0, 10) + " · " + startsAt.substring(11, 16) + " · " + session.getRoomName());
        holder.seat.setText("Lugar " + ticket.getSeat().getLabel());
        holder.itemView.setOnClickListener(view -> listener.onTicketSelected(ticket));
    }
    @Override public int getItemCount() { return tickets.size(); }
    static final class Holder extends RecyclerView.ViewHolder { final TextView title, meta, seat; Holder(View v) { super(v); title=v.findViewById(R.id.ticket_title); meta=v.findViewById(R.id.ticket_meta); seat=v.findViewById(R.id.ticket_seat); } }
}
