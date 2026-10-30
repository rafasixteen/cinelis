package cinelis.network;

public interface ApiCallback<T> {
    void onSuccess(T value);
    void onError(ApiError error);
}
