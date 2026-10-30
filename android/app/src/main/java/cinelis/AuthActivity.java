package cinelis;

import android.os.Bundle;
import android.content.Intent;
import android.util.Patterns;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;

import cinelis.databinding.ActivityAuthBinding;
import cinelis.model.User;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;
import cinelis.navigation.AppNavigator;
import com.google.android.material.snackbar.Snackbar;

public class AuthActivity extends AppCompatActivity {
    private ActivityAuthBinding binding;
    private boolean registration;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityAuthBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());
        MainActivity.applySystemInsets(binding.getRoot());
        binding.bottomNavigation.getRoot().getMenu().findItem(R.id.accountFragment).setChecked(true);
        binding.bottomNavigation.getRoot().setOnItemSelectedListener(item -> {
            if (item.getItemId() == R.id.homeFragment || item.getItemId() == R.id.navigation_search) {
                setResult(RESULT_FIRST_USER, new Intent().putExtra("topLevelDestination", item.getItemId()));
                finish();
            } else {
                getIntent().putExtra(AppNavigator.ARG_RETURN_DESTINATION, item.getItemId());
            }
            return true;
        });
        binding.recoverPassword.setOnClickListener(view ->
                Snackbar.make(view, R.string.recover_demo, Snackbar.LENGTH_LONG).show());
        if (savedInstanceState != null) setRegistration(savedInstanceState.getBoolean("registration", false));
        binding.authToggle.setOnClickListener(view -> setRegistration(!registration));
        binding.authSubmit.setOnClickListener(view -> submit());
        binding.demoLogin.setOnClickListener(view -> {
            setRegistration(false);
            binding.emailInput.setText("ana@exemplo.pt");
            binding.passwordInput.setText("password123");
            submit();
        });
    }

    private void setRegistration(boolean enabled) {
        registration = enabled;
        binding.nameField.setVisibility(enabled ? View.VISIBLE : View.GONE);
        binding.authHeading.setText(enabled ? R.string.create_account : R.string.auth_title);
        binding.authSubmit.setText(enabled ? R.string.create_account : R.string.sign_in);
        binding.authToggle.setText(enabled ? R.string.sign_in : R.string.create_account);
        binding.authToggleHeading.setText(enabled ? R.string.already_have_account : R.string.no_account_yet);
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
        setSubmitting(true);
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
                setResult(RESULT_OK, getIntent());
                finish();
            }
            @Override public void onError(ApiError error) { finishError(error); }
        });
    }

    private void finishError(ApiError error) {
        if (binding == null) return;
        setSubmitting(false);
        showError(error.getMessage());
    }

    private void setSubmitting(boolean submitting) {
        binding.authSubmit.setEnabled(!submitting);
        binding.authToggle.setEnabled(!submitting);
        binding.demoLogin.setEnabled(!submitting);
    }

    private void showError(String message) {
        binding.authError.setText(message);
        binding.authError.setVisibility(View.VISIBLE);
    }

    @Override public void onSaveInstanceState(@NonNull Bundle outState) {
        super.onSaveInstanceState(outState);
        outState.putBoolean("registration", registration);
    }

    @Override protected void onDestroy() {
        ApiClient.get().cancel(this);
        binding = null;
        super.onDestroy();
    }
}
