package cinelis;

import android.os.Bundle;
import android.content.Intent;
import android.view.View;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;

import cinelis.databinding.ActivityMainBinding;
import cinelis.navigation.AppNavigator;
import cinelis.ui.home.HomeFragment;

public class MainActivity extends AppCompatActivity {
    private ActivityMainBinding binding;
    private boolean authenticationPending;
    private final ActivityResultLauncher<Intent> authentication = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                authenticationPending = false;
                AppNavigator navigator = new AppNavigator(navController());
                if (result.getResultCode() == RESULT_FIRST_USER && result.getData() != null) {
                    navigateFromBottomBar(result.getData().getIntExtra("topLevelDestination", R.id.homeFragment));
                } else if (result.getResultCode() == RESULT_OK && result.getData() != null) {
                    navigator.resumeAfterAuthentication(result.getData().getExtras());
                } else {
                    // Cancelled login must not leave a protected page stuck loading.
                    navigator.showHome();
                }
            });

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        authenticationPending = savedInstanceState != null
                && savedInstanceState.getBoolean("authenticationPending");
        WindowCompat.setDecorFitsSystemWindows(getWindow(), false);
        binding = ActivityMainBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());
        applySystemInsets(binding.getRoot());

        binding.bottomNavigation.getRoot().setOnItemSelectedListener(
                item -> navigateFromBottomBar(item.getItemId()));
        navController().addOnDestinationChangedListener((controller, destination, arguments) -> {
            int id = destination.getId();
            int selected = id == R.id.ticketFragment || id == R.id.ticketsFragment
                    ? R.id.ticketsFragment : id == R.id.accountFragment ? R.id.accountFragment : R.id.homeFragment;
            binding.bottomNavigation.getRoot().getMenu().findItem(selected).setChecked(true);
        });
    }

    public NavController navController() {
        NavHostFragment host = (NavHostFragment) getSupportFragmentManager()
                .findFragmentById(R.id.nav_host_fragment);
        if (host == null) {
            throw new IllegalStateException("Navigation host is unavailable");
        }
        return host.getNavController();
    }

    public void launchAuthentication(Bundle arguments) {
        if (authenticationPending) return;
        authenticationPending = true;
        authentication.launch(new Intent(this, AuthActivity.class).putExtras(arguments));
    }

    @Override protected void onSaveInstanceState(@NonNull Bundle outState) {
        outState.putBoolean("authenticationPending", authenticationPending);
        super.onSaveInstanceState(outState);
    }

    boolean navigateFromBottomBar(int id) {
        NavController navController = navController();
        if (id == R.id.homeFragment) {
            navController.popBackStack(R.id.homeFragment, false);
            return true;
        }
        if (id == R.id.navigation_search) {
            navController.popBackStack(R.id.homeFragment, false);
            binding.getRoot().post(this::focusHomeSearch);
            return true;
        }
        if (id == R.id.ticketsFragment || id == R.id.accountFragment) {
            if (navController.getCurrentDestination() == null
                    || navController.getCurrentDestination().getId() != id) {
                navController.navigate(id);
            }
            return true;
        }
        return false;
    }

    private void focusHomeSearch() {
        NavHostFragment host = (NavHostFragment) getSupportFragmentManager()
                .findFragmentById(R.id.nav_host_fragment);
        if (host != null && host.getChildFragmentManager().getPrimaryNavigationFragment() instanceof HomeFragment) {
            ((HomeFragment) host.getChildFragmentManager().getPrimaryNavigationFragment()).focusSearch();
        }
    }

    static void applySystemInsets(@NonNull View root) {
        ViewCompat.setOnApplyWindowInsetsListener(root, (view, windowInsets) -> {
            Insets bars = windowInsets.getInsets(WindowInsetsCompat.Type.systemBars());
            view.setPadding(bars.left, bars.top, bars.right, bars.bottom);
            return WindowInsetsCompat.CONSUMED;
        });
    }
}
