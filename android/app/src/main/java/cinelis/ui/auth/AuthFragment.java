package cinelis.ui.auth;

import android.os.Bundle;
import android.util.Patterns;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;

import cinelis.R;
import cinelis.databinding.FragmentAuthBinding;
import cinelis.model.User;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;

public class AuthFragment extends Fragment {
    public static final String ARG_RETURN_DESTINATION = AppNavigator.ARG_RETURN_DESTINATION;
    private FragmentAuthBinding binding;
    private boolean registration;

    @Nullable @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentAuthBinding.inflate(inflater, container, false);
        binding.authToggle.setOnClickListener(view -> setRegistration(!registration));
        binding.authSubmit.setOnClickListener(view -> submit());
        return binding.getRoot();
    }

    private void setRegistration(boolean enabled) {
        registration = enabled;
        binding.nameInput.setVisibility(enabled ? View.VISIBLE : View.GONE);
        binding.authHeading.setText(enabled ? R.string.create_account : R.string.auth_title);
        binding.authSubmit.setText(enabled ? R.string.create_account : R.string.sign_in);
        binding.authToggle.setText(enabled ? R.string.have_account : R.string.need_account);
        binding.authError.setVisibility(View.GONE);
    }

    private void submit() {
        String name = binding.nameInput.getText().toString().trim();
        String email = binding.emailInput.getText().toString().trim();
        String password = binding.passwordInput.getText().toString();
        if (!Patterns.EMAIL_ADDRESS.matcher(email).matches() || password.length() < 8
                || (registration && name.isEmpty())) {
            showError(getString(R.string.auth_invalid));
            return;
        }
        binding.authSubmit.setEnabled(false);
        if (registration) {
            ApiClient.get().register(name, email, password, this, new ApiCallback<User>() {
                @Override public void onSuccess(User value) { login(email, password); }
                @Override public void onError(ApiError error) { finishError(error); }
            });
        } else login(email, password);
    }

    private void login(String email, String password) {
        ApiClient.get().login(email, password, this, new ApiCallback<User>() {
            @Override public void onSuccess(User value) {
                if (binding == null) return;
                NavController controller = NavHostFragment.findNavController(AuthFragment.this);
                String sessionId = getArguments() == null ? null : getArguments().getString(AppNavigator.ARG_SESSION_ID);
                controller.popBackStack();
                AppNavigator navigator = new AppNavigator(controller);
                if (sessionId != null) navigator.showSeats(sessionId); else navigator.showAccount();
            }
            @Override public void onError(ApiError error) { finishError(error); }
        });
    }

    private void finishError(ApiError error) {
        if (binding == null) return;
        binding.authSubmit.setEnabled(true);
        showError(error.getMessage());
    }

    private void showError(String message) {
        binding.authError.setText(message);
        binding.authError.setVisibility(View.VISIBLE);
    }

    @Override public void onDestroyView() {
        ApiClient.get().cancel(this);
        binding = null;
        super.onDestroyView();
    }
}
