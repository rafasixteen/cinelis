package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.Espresso.pressBack;
import static androidx.test.espresso.action.ViewActions.click;
import static androidx.test.espresso.action.ViewActions.scrollTo;
import static androidx.test.espresso.matcher.ViewMatchers.withId;
import static androidx.test.espresso.matcher.ViewMatchers.withText;
import static androidx.test.espresso.matcher.ViewMatchers.withContentDescription;
import static org.hamcrest.Matchers.allOf;
import static org.junit.Assert.assertTrue;

import android.graphics.Bitmap;
import android.graphics.Rect;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatDelegate;
import androidx.test.core.app.ActivityScenario;
import androidx.test.platform.app.InstrumentationRegistry;
import org.junit.Test;
import java.io.File;
import java.io.FileOutputStream;
import java.util.concurrent.CountDownLatch;
import java.util.concurrent.TimeUnit;
import cinelis.model.User;
import cinelis.navigation.AppNavigator;
import cinelis.network.ApiCallback;
import cinelis.network.ApiClient;
import cinelis.network.ApiError;

/** Captures the eight mockup screens in both themes for visual review. */
public class VisualFidelityTest {
    @Test public void mockupScreensRetainTheirLayoutAndActionsInBothThemes() throws Exception {
        int original = AppCompatDelegate.getDefaultNightMode();
        try {
            captureTheme("light", AppCompatDelegate.MODE_NIGHT_NO);
            captureTheme("dark", AppCompatDelegate.MODE_NIGHT_YES);
        } finally {
            InstrumentationRegistry.getInstrumentation().runOnMainSync(() ->
                    AppCompatDelegate.setDefaultNightMode(original));
        }
    }

    private void captureTheme(String theme, int mode) throws Exception {
        InstrumentationRegistry.getInstrumentation().runOnMainSync(() -> AppCompatDelegate.setDefaultNightMode(mode));
        loginDemo();
        try (ActivityScenario<MainActivity> scenario = ActivityScenario.launch(MainActivity.class)) {
            EspressoWait.until(withId(R.id.movie_sessions));
            scenario.onActivity(a -> assertNavigationLabelsVisible(a.findViewById(R.id.bottom_navigation)));
            capture(theme + "-01-home");
            scenario.onActivity(a -> new AppNavigator(a.navController()).showMovie("00000001-0000-4000-8000-000000000001"));
            EspressoWait.until(withText("18:00"));
            scenario.onActivity(a -> {
                View poster = a.findViewById(R.id.detail_poster), title = a.findViewById(R.id.movie_title);
                int[] p = new int[2], t = new int[2];
                poster.getLocationOnScreen(p); title.getLocationOnScreen(t);
                assertTrue("Movie details must sit beside the poster", p[0] + poster.getWidth() <= t[0]);
                assertTrue(a.findViewById(R.id.date_today).isSelected());
            });
            capture(theme + "-02-movie");
            String sessionId = theme.equals("light") ? "00000020-0000-4000-8000-000000000020"
                    : "00000023-0000-4000-8000-000000000023";
            scenario.onActivity(a -> new AppNavigator(a.navController()).showSeats(sessionId));
            EspressoWait.until(withContentDescription("Lugar A4, livre"));
            onView(withContentDescription("Lugar A4, livre")).perform(click());
            capture(theme + "-04-seats");
            onView(withId(R.id.continue_button)).perform(scrollTo(), click());
            EspressoWait.until(withId(R.id.checkout_quantity));
            EspressoWait.until(allOf(withId(R.id.checkout_total), withText("7,50 €")));
            EspressoWait.until(allOf(withId(R.id.checkout_seats), withText("Lugares A4")));
            capture(theme + "-05-checkout");
            pressBack();
            EspressoWait.until(withId(R.id.zoom_in));
            // The hold remains owned by the demo user; no purchase is made by this capture test.
            scenario.onActivity(a -> new AppNavigator(a.navController()).showTicket("00000091-0000-4000-8000-000000000091"));
            EspressoWait.until(allOf(withId(R.id.ticket_code), withText("CIN-DEMO-2")));
            capture(theme + "-06-ticket");
            scenario.onActivity(a -> new AppNavigator(a.navController()).showTickets());
            EspressoWait.until(withId(R.id.view_ticket));
            scenario.onActivity(a -> assertTrue(a.findViewById(R.id.upcoming_tab).isSelected()));
            capture(theme + "-07-tickets");
            scenario.onActivity(a -> new AppNavigator(a.navController()).showAccount());
            EspressoWait.until(withContentDescription("Compra #00000082"));
            capture(theme + "-08-account");
            scenario.onActivity(a -> {
                ApiClient.get().clearAuthentication();
                a.launchAuthentication(new android.os.Bundle());
            });
            EspressoWait.until(withId(R.id.email_input));
            capture(theme + "-03-auth");
            pressBack();
        }
    }

    private void loginDemo() throws Exception {
        CountDownLatch ready = new CountDownLatch(1);
        final boolean[] success = {false};
        InstrumentationRegistry.getInstrumentation().runOnMainSync(() ->
                ApiClient.get().login("ana@exemplo.pt", "password123", this, new ApiCallback<User>() {
                    @Override public void onSuccess(User user) { success[0] = true; ready.countDown(); }
                    @Override public void onError(ApiError error) { ready.countDown(); }
                }));
        assertTrue("Demo login", ready.await(5, TimeUnit.SECONDS) && success[0]);
    }

    private void capture(String name) throws Exception {
        InstrumentationRegistry.getInstrumentation().waitForIdleSync();
        // Wait for the next rendered frame/accessibility updates, not just the Volley callback.
        InstrumentationRegistry.getInstrumentation().getUiAutomation().waitForIdle(200, 5000);
        File directory = new File(InstrumentationRegistry.getArguments().getString("additionalTestOutputDir",
                InstrumentationRegistry.getInstrumentation().getTargetContext().getExternalFilesDir("visual").getPath()));
        assertTrue(directory.isDirectory() || directory.mkdirs());
        Bitmap bitmap = InstrumentationRegistry.getInstrumentation().getUiAutomation().takeScreenshot();
        try (FileOutputStream out = new FileOutputStream(new File(directory, name + ".png"))) {
            bitmap.compress(Bitmap.CompressFormat.PNG, 100, out);
        } finally { bitmap.recycle(); }
    }

    private void assertNavigationLabelsVisible(View view) {
        if (view instanceof TextView && view.isShown()) {
            TextView label = (TextView) view;
            Rect visible = new Rect();
            assertTrue("Navigation label must not be clipped by its parent", label.getGlobalVisibleRect(visible)
                    && visible.height() >= label.getHeight());
            assertTrue("Navigation label must have room for its text", label.getLayout() != null
                    && label.getLayout().getLineBottom(0) <= label.getHeight()
                    - label.getCompoundPaddingTop() - label.getCompoundPaddingBottom());
        }
        if (view instanceof ViewGroup) {
            ViewGroup group = (ViewGroup) view;
            for (int i = 0; i < group.getChildCount(); i++) assertNavigationLabelsVisible(group.getChildAt(i));
        }
    }
}
