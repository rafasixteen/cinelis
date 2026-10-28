package cinelis.model;

public final class User {
    private final String id;
    private final String name;
    private final String email;
    private final String role;
    private final String createdAt;

    public User(String id, String name, String email, String role, String createdAt) {
        this.id = id;
        this.name = name;
        this.email = email;
        this.role = role;
        this.createdAt = createdAt;
    }

    public String getId() { return id; }
    public String getName() { return name; }
    public String getEmail() { return email; }
    public String getRole() { return role; }
    public String getCreatedAt() { return createdAt; }
}
