# CineLis Android prototype

This folder contains the native Android UI prototype for CineLis. It is a Java project built from an Empty Views Activity and uses one `MainActivity` with Navigation fragments for the eight mobile mockup destinations.

## Open in Android Studio

Open the `android/` directory as the project. The project uses Java 17, Gradle 9.3.1, Android Gradle Plugin 9.1.1, `compileSdk`/`targetSdk` 37, and `minSdk` 24.

From the command line:

```bash
cd android
JAVA_HOME=/opt/android-studio/jbr ./gradlew assembleDebug
```

The debug APK is generated at `app/build/outputs/apk/debug/app-debug.apk`.

Run the local verification suite with:

```bash
JAVA_HOME=/opt/android-studio/jbr ./gradlew testDebugUnitTest assembleDebug lintDebug
```

With an emulator or unlocked USB device connected:

```bash
JAVA_HOME=/opt/android-studio/jbr ./gradlew connectedDebugAndroidTest
```

## Demo flow

Use the seeded customer account:

- Email: `ana@exemplo.pt`
- Password: `password123`

The prototype supports catalogue search, movie/session details, login and registration, a five-by-eight seat map, temporary holds, simulated checkout, ticket detail/list, account history, profile editing, theme selection, server URL settings, logout, and simulated account deletion.

## In-app Volley mock

The app deliberately makes no real HTTP requests. Standard Volley requests are passed to a custom in-process `Network`, which adds a deterministic 250 ms delay and routes them through a volatile `MockApiStore`. The router simulates the operations documented in [`docs/openapi.yaml`](../docs/openapi.yaml):

- login, logout, registration, and `GET|PUT|DELETE /auth/me`;
- movie list/search/detail and movie sessions;
- session detail, seat availability, and seat holds;
- order creation/list/detail;
- ticket list/detail.

Mock users, tokens, holds, orders, tickets, and account mutations live only in process memory. Force-stopping/restarting the process recreates the seed data. Auth tokens are never written to preferences.

Only these user preferences persist:

- light/dark/system theme;
- the configured API server URL.

The URL setting still feeds the normal Volley request URLs, but the custom mock transport intercepts them in this prototype.

## Deferred scope

SQLite and offline synchronization are intentionally not implemented. A real API service, durable authentication, real payments, ticket downloads/QR rendering, push messaging, and production networking are also deferred. These boundaries keep this iteration focused on the native mockup styling, activities/fragments, navigation, and end-to-end feel of the mobile app.
