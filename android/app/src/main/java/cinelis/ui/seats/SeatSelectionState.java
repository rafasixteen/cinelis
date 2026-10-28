package cinelis.ui.seats;

import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;

import cinelis.model.Seat;

public final class SeatSelectionState {
    private final Map<String, Seat> seats = new LinkedHashMap<>();
    private final Set<String> selected = new LinkedHashSet<>();
    private final double price;

    public SeatSelectionState(List<Seat> seats, double price) {
        for (Seat seat : seats) this.seats.put(seat.getId(), seat);
        this.price = price;
    }

    public boolean toggle(String seatId) {
        Seat seat = seats.get(seatId);
        if (seat == null || !"available".equals(seat.getStatus())) return false;
        if (!selected.remove(seatId)) selected.add(seatId);
        return true;
    }

    public boolean isSelected(String seatId) { return selected.contains(seatId); }

    public ArrayList<String> selectedSeatIds() {
        ArrayList<String> result = new ArrayList<>();
        for (String id : seats.keySet()) if (selected.contains(id)) result.add(id);
        return result;
    }

    public List<String> selectedLabels() {
        List<String> result = new ArrayList<>();
        for (String id : seats.keySet()) if (selected.contains(id)) result.add(seats.get(id).getLabel());
        return result;
    }

    public double total() { return selected.size() * price; }
}
