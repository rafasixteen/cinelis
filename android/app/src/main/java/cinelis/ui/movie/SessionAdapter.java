package cinelis.ui.movie;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import cinelis.R;
import cinelis.model.CinemaSession;

public final class SessionAdapter extends RecyclerView.Adapter<SessionAdapter.Holder> {
    public interface Listener { void onSessionSelected(CinemaSession session); }
    private final List<CinemaSession> sessions = new ArrayList<>();
    private final Listener listener;

    public SessionAdapter(Listener listener) { this.listener = listener; }
    public void submit(List<CinemaSession> values) { sessions.clear(); sessions.addAll(values); notifyDataSetChanged(); }

    @NonNull @Override public Holder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new Holder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_session, parent, false));
    }
    @Override public void onBindViewHolder(@NonNull Holder holder, int position) {
        CinemaSession session = sessions.get(position);
        String startsAt = session.getStartsAt();
        holder.time.setText(startsAt.length() >= 16 ? startsAt.substring(11, 16) : startsAt);
        holder.room.setText(session.getRoomName());
        holder.price.setText(String.format(Locale.forLanguageTag("pt-PT"), "· %.2f €", session.getPrice()));
        holder.itemView.setOnClickListener(view -> listener.onSessionSelected(session));
    }
    @Override public int getItemCount() { return sessions.size(); }

    static final class Holder extends RecyclerView.ViewHolder {
        final TextView time, room, price;
        Holder(View view) { super(view); time = view.findViewById(R.id.session_time); room = view.findViewById(R.id.session_room); price = view.findViewById(R.id.session_price); }
    }
}
