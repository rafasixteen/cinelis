package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.action.ViewActions.click;
import static androidx.test.espresso.action.ViewActions.closeSoftKeyboard;
import static androidx.test.espresso.action.ViewActions.replaceText;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static androidx.test.espresso.matcher.ViewMatchers.isDisplayed;
import static androidx.test.espresso.matcher.ViewMatchers.isDescendantOfA;
import static androidx.test.espresso.matcher.ViewMatchers.withText;
import static androidx.test.espresso.matcher.ViewMatchers.withId;
import static org.hamcrest.Matchers.allOf;

import androidx.test.ext.junit.rules.ActivityScenarioRule;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

import cinelis.network.ApiClient;

@RunWith(AndroidJUnit4.class)
public class CatalogAndAuthFlowTest {
    @Rule
    public ActivityScenarioRule<MainActivity> activityRule =
            new ActivityScenarioRule<>(MainActivity.class);

    @Test
    public void catalogueSessionAuthenticationContinuesToSeatSelection() {
        activityRule.getScenario().onActivity(activity -> ApiClient.get().clearAuthentication());
        onView(withText("O seu próximo grande filme.")).check(matches(isDisplayed()));
        EspressoWait.until(allOf(withText("A última luz"), isDisplayed()));
        onView(allOf(withText("A última luz"), isDescendantOfA(
                androidx.test.espresso.matcher.ViewMatchers.withId(R.id.current_movies)))).perform(click());
        EspressoWait.until(allOf(withText("Escolha uma sessão"), isDisplayed()));
        onView(withText("Escolha uma sessão")).check(matches(isDisplayed()));
        onView(allOf(withText("18:00"), isDisplayed())).perform(click());
        EspressoWait.until(withText("Bem-vindo de volta."));
        androidx.test.platform.app.InstrumentationRegistry.getInstrumentation().runOnMainSync(() -> {
            android.app.Activity resumed = androidx.test.runner.lifecycle.ActivityLifecycleMonitorRegistry
                    .getInstance().getActivitiesInStage(androidx.test.runner.lifecycle.Stage.RESUMED)
                    .iterator().next();
            org.junit.Assert.assertEquals("AuthActivity", resumed.getClass().getSimpleName());
            resumed.recreate();
        });
        EspressoWait.until(withId(R.id.email_input));
        onView(withId(R.id.email_input)).perform(replaceText("ana@exemplo.pt"), closeSoftKeyboard());
        onView(withId(R.id.password_input)).perform(replaceText("password123"), closeSoftKeyboard());
        onView(withId(R.id.auth_submit)).perform(click());
        EspressoWait.until(withText("Escolha os seus lugares"));
    }

}
