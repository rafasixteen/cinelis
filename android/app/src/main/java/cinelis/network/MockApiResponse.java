package cinelis.network;

import java.nio.charset.StandardCharsets;
import java.util.Collections;
import java.util.LinkedHashMap;
import java.util.Map;

public final class MockApiResponse {
    private final int statusCode;
    private final Map<String, String> headers;
    private final byte[] body;

    public MockApiResponse(int statusCode, Map<String, String> headers, byte[] body) {
        this.statusCode = statusCode;
        this.headers = Collections.unmodifiableMap(new LinkedHashMap<>(headers));
        this.body = body == null ? new byte[0] : body.clone();
    }

    public static MockApiResponse json(int statusCode, String json) {
        Map<String, String> headers = new LinkedHashMap<>();
        headers.put("Content-Type", "application/json; charset=utf-8");
        return new MockApiResponse(statusCode, headers, json.getBytes(StandardCharsets.UTF_8));
    }

    public static MockApiResponse empty(int statusCode) {
        return new MockApiResponse(statusCode, Collections.emptyMap(), new byte[0]);
    }

    public int getStatusCode() { return statusCode; }
    public Map<String, String> getHeaders() { return headers; }
    public byte[] getBody() { return body.clone(); }
}
