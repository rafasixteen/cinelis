package cinelis.network;

import org.json.JSONObject;

final class MockApiException extends RuntimeException {
    private final int statusCode;
    private final String code;
    private final JSONObject fields;

    MockApiException(int statusCode, String code, String message) {
        this(statusCode, code, message, null);
    }

    MockApiException(int statusCode, String code, String message, JSONObject fields) {
        super(message);
        this.statusCode = statusCode;
        this.code = code;
        this.fields = fields;
    }

    int getStatusCode() { return statusCode; }
    String getCode() { return code; }
    JSONObject getFields() { return fields; }
}
