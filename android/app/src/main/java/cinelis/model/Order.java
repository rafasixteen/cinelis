package cinelis.model;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

public final class Order {
    private final String id;
    private final double total;
    private final String createdAt;
    private final List<Ticket> tickets;

    public Order(String id, double total, String createdAt, List<Ticket> tickets) {
        this.id = id;
        this.total = total;
        this.createdAt = createdAt;
        this.tickets = Collections.unmodifiableList(new ArrayList<>(tickets));
    }

    public String getId() { return id; }
    public double getTotal() { return total; }
    public String getCreatedAt() { return createdAt; }
    public List<Ticket> getTickets() { return tickets; }
}
