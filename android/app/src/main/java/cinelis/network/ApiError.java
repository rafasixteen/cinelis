package cinelis.network;

import org.json.JSONObject;

public final class ApiError {
    private final int statusCode;
    private final String code;
    private final String message;
    private final JSONObject fields;

    public ApiError(int statusCode, String code, String message, JSONObject fields) {
        this.statusCode = statusCode;
        this.code = code;
        this.message = message;
        this.fields = fields;
    }

    public int getStatusCode() { return statusCode; }
    public String getCode() { return code; }
    public String getMessage() { return message; }
    public JSONObject getFields() { return fields; }
}
