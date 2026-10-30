package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.action.ViewActions.closeSoftKeyboard;
import static androidx.test.espresso.action.ViewActions.replaceText;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static androidx.test.espresso.matcher.ViewMatchers.isDisplayed;
import static androidx.test.espresso.matcher.ViewMatchers.withId;
import static androidx.test.espresso.matcher.ViewMatchers.withText;
import static org.hamcrest.Matchers.allOf;

import androidx.test.ext.junit.rules.ActivityScenarioRule;
import org.junit.Rule;
import org.junit.Test;

public class SearchRecoveryTest {
    @Rule public ActivityScenarioRule<MainActivity> activityRule =
            new ActivityScenarioRule<>(MainActivity.class);

    @Test public void noResultsKeepsSearchEditableAndCanRecoverWithoutLeavingHome() {
        EspressoWait.until(allOf(withText("A última luz"), isDisplayed()));
        onView(withId(R.id.search_input)).perform(replaceText("zzzznonexistent"), closeSoftKeyboard());
        onView(withId(R.id.search_input)).check(matches(isDisplayed()));
        onView(withId(R.id.empty_container)).check(matches(isDisplayed()));
        activityRule.getScenario().recreate();
        EspressoWait.until(withId(R.id.empty_container));
        onView(withId(R.id.search_input)).check(matches(withText("zzzznonexistent")));
        onView(withId(R.id.search_input)).perform(replaceText("Órbita"), closeSoftKeyboard());
        EspressoWait.until(allOf(withText("Órbita"), isDisplayed()));
        onView(withId(R.id.search_input)).perform(replaceText(""), closeSoftKeyboard());
        EspressoWait.until(allOf(withText("A última luz"), isDisplayed()));
    }
}
