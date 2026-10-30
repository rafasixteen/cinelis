package cinelis;

import static androidx.test.espresso.Espresso.onView;
import static androidx.test.espresso.matcher.ViewMatchers.isRoot;

import android.view.View;
import android.view.ViewGroup;
import android.app.Activity;

import androidx.test.espresso.UiController;
import androidx.test.espresso.ViewAction;
import androidx.test.runner.lifecycle.ActivityLifecycleMonitorRegistry;
import androidx.test.runner.lifecycle.Stage;

import org.hamcrest.Matcher;

final class EspressoWait {
    private EspressoWait() {}

    static void until(Matcher<View> matcher) {
        onView(isRoot()).perform(new ViewAction() {
            @Override public Matcher<View> getConstraints() { return isRoot(); }
            @Override public String getDescription() { return "wait for a matching view"; }

            @Override public void perform(UiController controller, View root) {
                long deadline = System.currentTimeMillis() + 5_000L;
                do {
                    // A login result can finish AuthActivity while this action is waiting.
                    // Resolve the active decor again instead of polling its detached old root.
                    if (root.isAttachedToWindow() && root.hasWindowFocus() && find(root, matcher)) return;
                    for (Activity activity : ActivityLifecycleMonitorRegistry.getInstance()
                            .getActivitiesInStage(Stage.RESUMED)) {
                        View decor = activity.getWindow().getDecorView();
                        if (decor.hasWindowFocus() && find(decor, matcher)) return;
                    }
                    controller.loopMainThreadForAtLeast(50L);
                } while (System.currentTimeMillis() < deadline);
                throw new AssertionError("Timed out waiting for: " + matcher);
            }
        });
    }

    private static boolean find(View view, Matcher<View> matcher) {
        if (view.isShown() && matcher.matches(view)) return true;
        if (!(view instanceof ViewGroup)) return false;
        ViewGroup group = (ViewGroup) view;
        for (int index = 0; index < group.getChildCount(); index++) {
            if (find(group.getChildAt(index), matcher)) return true;
        }
        return false;
    }
}
