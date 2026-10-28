package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.action.ViewActions.click;
import static androidx.test.espresso.assertion.ViewAssertions.matches;
import static androidx.test.espresso.matcher.ViewMatchers.isDisplayed;
import static androidx.test.espresso.matcher.ViewMatchers.isDescendantOfA;
import static androidx.test.espresso.matcher.ViewMatchers.withText;
import static org.hamcrest.Matchers.allOf;

import androidx.test.ext.junit.rules.ActivityScenarioRule;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

@RunWith(AndroidJUnit4.class)
public class CatalogAndAuthFlowTest {
    @Rule
    public ActivityScenarioRule<MainActivity> activityRule =
            new ActivityScenarioRule<>(MainActivity.class);

    @Test
    public void seededCatalogueOpensMovieSessions() throws Exception {
        onView(withText("O seu próximo grande filme.")).check(matches(isDisplayed()));
        waitForMock();
        onView(allOf(withText("A última luz"), isDescendantOfA(
                androidx.test.espresso.matcher.ViewMatchers.withId(R.id.current_movies)))).perform(click());
        waitForMock();
        onView(withText("Escolha uma sessão")).check(matches(isDisplayed()));
        onView(allOf(withText("20:30"), isDisplayed())).check(matches(isDisplayed()));
    }

    private static void waitForMock() throws InterruptedException {
        Thread.sleep(500L);
    }
}
