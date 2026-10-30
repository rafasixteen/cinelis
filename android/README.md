# CineLis Android prototype

This folder contains the native Android UI prototype for CineLis, using Java and XML Views. `MainActivity` hosts seven Navigation fragments; `AuthActivity` handles login and registration separately and returns its result to the requested screen.

## Activities and fragments (AMSI brief, page 2)

The brief requires at least **6 Activity and/or Fragment components**, recommends a maximum of **10**, and requires some functionality in fragments. This app has **2 activities + 7 product fragments = 9** (eight product screens plus the main shell):

| Component | Responsibility |
| --- | --- |
| `MainActivity` | Main shell, bottom navigation, authentication result handling |
| `AuthActivity` | Login and registration; user-entered name, email and password |
| `HomeFragment` | Dynamic catalogue and editable movie search |
| `MovieDetailFragment` | Movie details and dynamic session list (catalogue master/detail) |
| `SeatSelectionFragment` | Dynamic seat map and seat selection |
| `CheckoutFragment` | Purchase summary and user-entered phone number |
| `TicketFragment` | Purchased ticket details (tickets master/detail) |
| `TicketsFragment` | Upcoming and past tickets |
| `AccountFragment` | Profile, order history, profile editing and configuration dialogs |

Authentication and checkout provide two data-entry screens. Catalogue, sessions, seats, tickets and account history are populated asynchronously through Volley. Registration/profile routes simulate all four CRUD operations; server settings and theme use SharedPreferences. This covers the current UI prototype scope; the brief's production API and SQLite requirements remain deferred.

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

The prototype opens **already signed in as Ana Martins**, so Account, purchase history and Tickets are populated immediately. The seed includes three purchases across three films: two upcoming tickets and one past ticket, with matching order/session/detail responses.

To test authentication, sign out from Account. Use **Usar conta de demonstração** on the login screen, or enter:

- Email: `ana@exemplo.pt`
- Password: `password123`

The prototype supports catalogue search, movie/session details, login and registration, a five-by-eight seat map, temporary holds, simulated checkout, ticket detail/list, account history, profile editing, theme selection, server URL settings, logout, and simulated account deletion. Home presents a daily programme: choose **Hoje**, **Amanhã**, or **30 out.**, then tap an inline session time to go directly to seat selection; tapping the movie summary still opens its details.

## Visual system

The native Views use the light/dark colors from `common/assets/css/layouts/base.css` and the spacing, type scale and component placement in `cinelis-mobile-mockups.excalidraw` / `cinelis-ui-tokens.excalidraw`:

- the actual web CineLis logos and bundled Inter font (license in [`FONT-LICENSE.txt`](FONT-LICENSE.txt));
- shared 64 dp header, 72 dp navigation bar, 20 dp page gutters, 25 sp page titles and 4/6/8 dp corner radii;
- flat primary/secondary buttons, selected date/ticket tabs, compact catalogue cards, side-by-side movie details, seat-map legends/zoom and an illustrated ticket QR;
- light/dark resources, with scrollable content to accommodate native system bars and smaller displays.

Account's **⋮** button reveals profile editing, theme/server configuration and account deletion. These prototype tools stay outside the mockup's main profile/purchase-history layout. Search remains accessible when no movies match.

`VisualFidelityTest` captures the eight mockup screens in both themes and checks key placement/selection states and navigation-label clipping. Android test screenshots are written to the runner's `additionalTestOutputDir`, or the app's external `files/visual` directory when running instrumentation directly. They are review artifacts, not pixel-diff golden tests.

## In-app Volley mock

The app deliberately makes no real HTTP requests. Standard Volley requests are passed to a custom in-process `Network`, which adds a deterministic 250 ms delay and routes them through a volatile `MockApiStore`. The router simulates the operations documented in [`docs/openapi.yaml`](../docs/openapi.yaml):

- login, logout, registration, and `GET|PUT|DELETE /auth/me`;
- movie list/search/detail and movie sessions;
- session detail, seat availability, and seat holds;
- order creation/list/detail;
- ticket list/detail.

Mock users, tokens, holds, orders, tickets, and account mutations live only in process memory. Force-stopping/restarting the process recreates the seed data and demo session. Signing out stays signed out during that process; creating a new account produces an empty account, while the demo shortcut returns to Ana's populated account. Auth tokens are never written to preferences.

Like the web previews, the catalogue uses fixed presentation dates (28–30 October 2026); the upcoming/past split is relative to 28 October 2026, not the device clock. These fixtures stay browsable when the prototype is demonstrated later.

Only these user preferences persist:

- light/dark/system theme;
- the configured API server URL.

The URL setting still feeds the normal Volley request URLs, but the custom mock transport intercepts them in this prototype.

## Deferred scope

SQLite and offline synchronization are intentionally not implemented. A real API service, durable authentication, real payments, ticket downloads/QR rendering, push messaging, and production networking are also deferred. These boundaries keep this iteration focused on the native mockup styling, activities/fragments, navigation, and end-to-end feel of the mobile app.
