package cinelis.model;

public final class Ticket {
    private final String id;
    private final String orderId;
    private final CinemaSession session;
    private final Seat seat;
    private final double price;
    private final String code;
    private final String createdAt;
    private final String usedAt;

    public Ticket(String id, String orderId, CinemaSession session, Seat seat, double price,
                  String code, String createdAt, String usedAt) {
        this.id = id;
        this.orderId = orderId;
        this.session = session;
        this.seat = seat;
        this.price = price;
        this.code = code;
        this.createdAt = createdAt;
        this.usedAt = usedAt;
    }

    public String getId() { return id; }
    public String getOrderId() { return orderId; }
    public CinemaSession getSession() { return session; }
    public Seat getSeat() { return seat; }
    public double getPrice() { return price; }
    public String getCode() { return code; }
    public String getCreatedAt() { return createdAt; }
    public String getUsedAt() { return usedAt; }
}
