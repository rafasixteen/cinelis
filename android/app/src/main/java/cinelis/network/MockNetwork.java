package cinelis.network;

import com.android.volley.AuthFailureError;
import com.android.volley.Header;
import com.android.volley.Network;
import com.android.volley.NetworkResponse;
import com.android.volley.Request;
import com.android.volley.VolleyError;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Map;

public final class MockNetwork implements Network {
    public static final long DELAY_MILLIS = 250L;
    private final MockApiRouter router;

    public MockNetwork(MockApiRouter router) {
        this.router = router;
    }

    @Override
    public NetworkResponse performRequest(Request<?> request) throws VolleyError {
        long startedAt = System.currentTimeMillis();
        try {
            Thread.sleep(DELAY_MILLIS);
            Map<String, String> headers = request.getHeaders();
            byte[] body = request.getBody();
            MockApiResponse result = router.route(request.getMethod(), request.getUrl(),
                    headers == null ? Collections.emptyMap() : headers, body);
            NetworkResponse response = new NetworkResponse(result.getStatusCode(), result.getBody(),
                    false, System.currentTimeMillis() - startedAt, toHeaders(result.getHeaders()));
            if (result.getStatusCode() >= 400) throw new VolleyError(response);
            return response;
        } catch (AuthFailureError exception) {
            throw new VolleyError(exception);
        } catch (InterruptedException exception) {
            Thread.currentThread().interrupt();
            throw new VolleyError(exception);
        }
    }

    private static List<Header> toHeaders(Map<String, String> headers) {
        List<Header> result = new ArrayList<>(headers.size());
        for (Map.Entry<String, String> entry : headers.entrySet()) {
            result.add(new Header(entry.getKey(), entry.getValue()));
        }
        return result;
    }
}
