package cinelis.network;

import com.android.volley.RequestQueue;
import com.android.volley.toolbox.NoCache;

public final class MockRequestQueueFactory {
    private MockRequestQueueFactory() {}

    public static RequestQueue create(MockApiRouter router) {
        RequestQueue queue = new RequestQueue(new NoCache(), new MockNetwork(router));
        queue.start();
        return queue;
    }
}
