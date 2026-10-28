package cinelis.ui.seats;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

import cinelis.R;
import cinelis.model.Seat;

public final class SeatGridAdapter extends RecyclerView.Adapter<SeatGridAdapter.Holder> {
    public interface Listener { void onSeatSelected(Seat seat); }
    private final List<Seat> seats = new ArrayList<>();
    private final Listener listener;
    private SeatSelectionState state;

    public SeatGridAdapter(Listener listener) { this.listener = listener; }
    public void submit(List<Seat> values, SeatSelectionState selectionState) {
        seats.clear(); seats.addAll(values); state = selectionState; notifyDataSetChanged();
    }
    public void refresh() { notifyDataSetChanged(); }

    @NonNull @Override public Holder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new Holder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_seat, parent, false));
    }
    @Override public void onBindViewHolder(@NonNull Holder holder, int position) {
        Seat seat = seats.get(position);
        holder.label.setText(seat.getLabel());
        boolean selected = state != null && state.isSelected(seat.getId());
        int background = selected ? R.drawable.bg_seat_selected :
                ("available".equals(seat.getStatus()) ? R.drawable.bg_seat_available : R.drawable.bg_seat_occupied);
        holder.label.setBackgroundResource(background);
        holder.label.setEnabled("available".equals(seat.getStatus()));
        holder.label.setContentDescription("Lugar " + seat.getLabel() + ", " + (selected ? "selecionado" : seat.getStatus()));
        holder.itemView.setOnClickListener(view -> listener.onSeatSelected(seat));
    }
    @Override public int getItemCount() { return seats.size(); }
    static final class Holder extends RecyclerView.ViewHolder {
        final TextView label;
        Holder(View view) { super(view); label = view.findViewById(R.id.seat_label); }
    }
}
