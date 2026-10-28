package cinelis.model;

public final class Seat {
    private final String id;
    private final String row;
    private final int number;
    private final String status;

    public Seat(String id, String row, int number, String status) {
        this.id = id;
        this.row = row;
        this.number = number;
        this.status = status;
    }

    public String getId() { return id; }
    public String getRow() { return row; }
    public int getNumber() { return number; }
    public String getStatus() { return status; }
    public String getLabel() { return row + number; }
}
