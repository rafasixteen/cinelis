package cinelis.ui.common;

import android.view.View;
import android.widget.TextView;

public final class UiStateController {
    private final View loading;
    private final View content;
    private final View empty;
    private final View error;
    private final TextView errorMessage;
    private final View retry;

    public UiStateController(View loading, View content, View empty, View error,
                             TextView errorMessage, View retry) {
        this.loading = loading;
        this.content = content;
        this.empty = empty;
        this.error = error;
        this.errorMessage = errorMessage;
        this.retry = retry;
    }

    public void showLoading() { show(loading); }
    public void showContent() { show(content); }
    public void showEmpty() { show(empty); }

    public void showError(String message, Runnable retryAction) {
        if (errorMessage != null) errorMessage.setText(message);
        if (retry != null) retry.setOnClickListener(view -> retryAction.run());
        show(error);
    }

    private void show(View selected) {
        loading.setVisibility(selected == loading ? View.VISIBLE : View.GONE);
        content.setVisibility(selected == content ? View.VISIBLE : View.GONE);
        empty.setVisibility(selected == empty ? View.VISIBLE : View.GONE);
        error.setVisibility(selected == error ? View.VISIBLE : View.GONE);
    }
}
