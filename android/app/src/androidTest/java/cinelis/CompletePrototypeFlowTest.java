package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.Espresso.pressBack;
import static androidx.test.espresso.action.ViewActions.click;
import static androidx.test.espresso.action.ViewActions.closeSoftKeyboard;
import static androidx.test.espresso.action.ViewActions.replaceText;
import static androidx.test.espresso.action.ViewActions.scrollTo;
import static androidx.test.espresso.matcher.ViewMatchers.withId;
import static androidx.test.espresso.matcher.ViewMatchers.withText;
import static androidx.test.espresso.matcher.ViewMatchers.withContentDescription;
import static androidx.test.espresso.matcher.ViewMatchers.isEnabled;
import static androidx.test.espresso.matcher.ViewMatchers.isDescendantOfA;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static org.hamcrest.Matchers.allOf;
import static org.hamcrest.Matchers.startsWith;
import static org.junit.Assert.assertEquals;

import androidx.test.ext.junit.rules.ActivityScenarioRule;
import androidx.test.ext.junit.runners.AndroidJUnit4;
import androidx.appcompat.app.AppCompatDelegate;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

import cinelis.navigation.AppNavigator;
import cinelis.network.ApiClient;
import cinelis.storage.PreferencesManager;

@RunWith(AndroidJUnit4.class)
public class CompletePrototypeFlowTest {
    @Rule public ActivityScenarioRule<MainActivity> activityRule =
            new ActivityScenarioRule<>(MainActivity.class);

    @Test public void loginPurchaseTicketHistoryAndPreferencesFormOneContinuousFlow() {
        activityRule.getScenario().onActivity(activity -> {
            ApiClient.get().clearAuthentication();
            activity.navigateFromBottomBar(R.id.accountFragment);
            activity.getSupportFragmentManager().executePendingTransactions();
        });
        EspressoWait.until(withText("Bem-vindo de volta."));
        onView(withId(R.id.email_input)).perform(replaceText("ana@exemplo.pt"), closeSoftKeyboard());
        onView(withId(R.id.password_input)).perform(replaceText("password123"), closeSoftKeyboard());
        onView(withId(R.id.auth_submit)).perform(click());
        EspressoWait.until(withText("Ana Martins"));

        activityRule.getScenario().onActivity(activity ->
                new AppNavigator(activity.navController()).showSeats(
                        "00000020-0000-4000-8000-000000000020"));
        EspressoWait.until(withContentDescription("Lugar A1, livre"));
        onView(withContentDescription("Lugar A1, livre")).perform(click());
        onView(withId(R.id.continue_button)).perform(scrollTo(), click());
        EspressoWait.until(withId(R.id.phone_input));
        pressBack();
        EspressoWait.until(allOf(withId(R.id.selected_labels), withText("A1")));
        onView(withId(R.id.selected_labels)).check(matches(withText("A1")));
        onView(withId(R.id.continue_button)).check(matches(isEnabled())).perform(click());
        EspressoWait.until(withId(R.id.phone_input));
        onView(withId(R.id.phone_input)).perform(replaceText("912345678"), closeSoftKeyboard());
        onView(withId(R.id.pay_button)).perform(scrollTo(), click());
        EspressoWait.until(allOf(withId(R.id.ticket_code), withText(startsWith("CIN-"))));
        onView(withId(R.id.ticket_seat)).check(matches(withText("Lugar A1")));

        activityRule.getScenario().onActivity(activity ->
                activity.navigateFromBottomBar(R.id.ticketsFragment));
        EspressoWait.until(allOf(withText("Lugar A1"),
                isDescendantOfA(withId(R.id.tickets_list))));
        activityRule.getScenario().onActivity(activity ->
                activity.navigateFromBottomBar(R.id.accountFragment));
        EspressoWait.until(withContentDescription("Compra #00000101"));
        onView(withId(R.id.account_settings_button)).perform(click());
        onView(withId(R.id.theme_button)).perform(scrollTo(), click());
        EspressoWait.until(withId(R.id.theme_dark));
        onView(withId(R.id.theme_dark)).perform(click());
        onView(withText("Guardar")).perform(click());
        activityRule.getScenario().recreate();
        EspressoWait.until(withText("A minha conta"));
        activityRule.getScenario().onActivity(activity -> assertEquals(
                AppCompatDelegate.MODE_NIGHT_YES,
                new PreferencesManager(activity).getThemeMode()));
    }
}
