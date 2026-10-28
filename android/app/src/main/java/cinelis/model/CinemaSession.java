package cinelis.model;

public final class CinemaSession {
    private final String id;
    private final Movie movie;
    private final String roomId;
    private final String roomName;
    private final String startsAt;
    private final double price;
    private final String status;

    public CinemaSession(String id, Movie movie, String roomId, String roomName, String startsAt,
                         double price, String status) {
        this.id = id;
        this.movie = movie;
        this.roomId = roomId;
        this.roomName = roomName;
        this.startsAt = startsAt;
        this.price = price;
        this.status = status;
    }

    public String getId() { return id; }
    public Movie getMovie() { return movie; }
    public String getRoomId() { return roomId; }
    public String getRoomName() { return roomName; }
    public String getStartsAt() { return startsAt; }
    public double getPrice() { return price; }
    public String getStatus() { return status; }
}
