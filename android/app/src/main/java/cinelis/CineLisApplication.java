package cinelis;

import android.app.Application;

import androidx.appcompat.app.AppCompatDelegate;

import cinelis.network.ApiClient;
import cinelis.network.MockApiRouter;
import cinelis.network.MockApiStore;
import cinelis.network.MockRequestQueueFactory;
import cinelis.storage.PreferencesManager;

public class CineLisApplication extends Application {
    @Override
    public void onCreate() {
        super.onCreate();
        PreferencesManager preferences = new PreferencesManager(this);
        AppCompatDelegate.setDefaultNightMode(preferences.getThemeMode());
        ApiClient.initialize(
                MockRequestQueueFactory.create(new MockApiRouter(new MockApiStore())),
                preferences
        );
    }
}
