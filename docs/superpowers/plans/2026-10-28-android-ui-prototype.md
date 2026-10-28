# CineLis Android UI Prototype Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Java/XML Android prototype under `android/` that reproduces the eight CineLis mobile mockups and runs the complete customer flow through an in-app Volley mock of `docs/openapi.yaml`.

**Architecture:** A single `MainActivity` hosts eight Navigation Component fragments and the mockup bottom bar. Fragments issue normal Volley JSON requests through `ApiClient`; a custom Volley `Network` delegates to an in-memory OpenAPI router and returns realistic asynchronous responses. SharedPreferences stores only theme and server URL; backend state is deliberately process-local and no SQLite/Room dependency exists.

**Tech Stack:** Java 17, XML Views/View Binding, Android API 37 (min 24), AGP 9.1.1, Gradle 9.3.1, AppCompat 1.8.0, Material 1.14.0, Navigation 2.10.2, RecyclerView 1.4.0, Volley 1.2.1, JUnit 4.13.2, AndroidX Test 1.3.0, Espresso 3.7.0.

**Spec:** `docs/superpowers/specs/2026-10-28-android-ui-prototype-design.md`

## Global Constraints

- Create a repository-root `android/` project with application ID `com.cinelis.app` and Java namespace/package `cinelis`.
- Use Java source and XML Views only; do not add Kotlin or Compose.
- Use one Empty Views style `MainActivity` and exactly eight product fragments matching AM-01 through AM-08.
- Keep all request delivery asynchronous through a real Volley `RequestQueue` backed by the in-app custom `Network`.
- Cover every HTTP operation currently defined in `docs/openapi.yaml`.
- Do not add SQLite, Room, disk caches, offline mode, a real API transport, or production payment logic.
- Mock backend state resets with the application process. Persist only theme mode and server base URL through SharedPreferences.
- Use Portuguese copy and the exact light/dark tokens in the approved spec.
- Recreate poster/brand visuals with XML/vector/shape resources; do not introduce remote image dependencies.
- Use `JAVA_HOME=/opt/android-studio/jbr` for Gradle commands on this machine.

## Review Focus

- A protected request with a missing or stale token must produce the login flow rather than a crash; pin this in Task 3's auth/navigation test.
- Empty or unmatched movie/ticket/order results must show a useful empty state; pin this in Tasks 3 and 5.
- A duplicate or unavailable seat hold must return 409 and leave checkout disabled; pin this in Tasks 2 and 4.
- Recreating the application must reseed backend data while retaining theme/server preferences only; pin this in Tasks 1 and 2.
- Malformed or slash-variant configured base URLs must normalize safely before mock route matching; pin this in Task 2.

---

### Task 1: Android project, theme system, and eight-destination shell

**Files:**
- Create: `android/settings.gradle`
- Create: `android/build.gradle`
- Create: `android/gradle.properties`
- Create: `android/gradle/wrapper/gradle-wrapper.properties`
- Generate: `android/gradle/wrapper/gradle-wrapper.jar`
- Generate: `android/gradlew`
- Generate: `android/gradlew.bat`
- Create: `android/app/build.gradle`
- Create: `android/app/proguard-rules.pro`
- Create: `android/app/src/main/AndroidManifest.xml`
- Create: `android/app/src/main/java/cinelis/CineLisApplication.java`
- Create: `android/app/src/main/java/cinelis/MainActivity.java`
- Create: `android/app/src/main/java/cinelis/navigation/AppNavigator.java`
- Create: `android/app/src/main/java/cinelis/storage/PreferencesManager.java`
- Create: `android/app/src/main/java/cinelis/ui/home/HomeFragment.java`
- Create: `android/app/src/main/java/cinelis/ui/movie/MovieDetailFragment.java`
- Create: `android/app/src/main/java/cinelis/ui/auth/AuthFragment.java`
- Create: `android/app/src/main/java/cinelis/ui/seats/SeatSelectionFragment.java`
- Create: `android/app/src/main/java/cinelis/ui/checkout/CheckoutFragment.java`
- Create: `android/app/src/main/java/cinelis/ui/ticket/TicketFragment.java`
- Create: `android/app/src/main/java/cinelis/ui/tickets/TicketsFragment.java`
- Create: `android/app/src/main/java/cinelis/ui/account/AccountFragment.java`
- Create: `android/app/src/main/res/layout/activity_main.xml`
- Create: `android/app/src/main/res/layout/fragment_home.xml`
- Create: `android/app/src/main/res/layout/fragment_movie_detail.xml`
- Create: `android/app/src/main/res/layout/fragment_auth.xml`
- Create: `android/app/src/main/res/layout/fragment_seat_selection.xml`
- Create: `android/app/src/main/res/layout/fragment_checkout.xml`
- Create: `android/app/src/main/res/layout/fragment_ticket.xml`
- Create: `android/app/src/main/res/layout/fragment_tickets.xml`
- Create: `android/app/src/main/res/layout/fragment_account.xml`
- Create: `android/app/src/main/res/navigation/main_nav_graph.xml`
- Create: `android/app/src/main/res/menu/menu_bottom_navigation.xml`
- Create: `android/app/src/main/res/values/colors.xml`
- Create: `android/app/src/main/res/values/dimens.xml`
- Create: `android/app/src/main/res/values/strings.xml`
- Create: `android/app/src/main/res/values/themes.xml`
- Create: `android/app/src/main/res/values-night/colors.xml`
- Create: `android/app/src/main/res/values-night/themes.xml`
- Create: `android/app/src/main/res/xml/backup_rules.xml`
- Create: `android/app/src/main/res/xml/data_extraction_rules.xml`
- Create: `android/app/src/androidTest/java/cinelis/MainNavigationTest.java`
- Create: `android/app/src/test/java/cinelis/storage/PreferencePolicyTest.java`

**Interfaces:**
- Consumes: approved design and mockup token values.
- Produces: `PreferencesManager(Context)`, `getServerUrl(): String`, `setServerUrl(String): void`, `getThemeMode(): int`, `setThemeMode(int): void`; `AppNavigator` route methods; eight fragment destination IDs and argument names `movieId`, `sessionId`, `seatIds`, `ticketId`, and `returnDestination`.

- [ ] **Step 1: Create the build-only project files**

Set Gradle/AGP/dependency versions from the header, enable View Binding, use `compileSdk 37`, `targetSdk 37`, `minSdk 24`, and Java 17. Generate the Gradle 9.3.1 wrapper without adding application source.

- [ ] **Step 2: Write the failing shell tests**

`MainNavigationTest` asserts that launching `MainActivity` renders Home, bottom navigation reaches Tickets and Account, and the navigation graph contains eight fragment destinations. `PreferencePolicyTest` asserts that the persistable-key allowlist contains only `theme_mode` and `server_url` so mock auth/backend state cannot accidentally become durable.

- [ ] **Step 3: Run the tests to verify failure**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew testDebugUnitTest connectedDebugAndroidTest`

Expected: FAIL because `PreferencesManager`, `MainActivity`, resources, and destinations do not exist.

- [ ] **Step 4: Implement the theme and navigation shell**

Implement `CineLisApplication` theme initialization, `PreferencesManager`, `MainActivity`, `AppNavigator`, the bottom bar, the navigation graph, and minimal View Binding fragment shells. Apply system-bar insets and map bottom actions to Home, Home-with-search-focus, Tickets, and Account.

- [ ] **Step 5: Add exact base theme resources**

Use the approved light/dark tokens, 4/8/12/16/20/24/32/40 dp spacing scale, 4/6/8 dp radii, 48 dp minimum touch height, Portuguese shared strings, and Android sans-serif weights approximating Inter.

- [ ] **Step 6: Run shell verification**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew testDebugUnitTest assembleDebug lintDebug`

Expected: PASS; if an emulator/device is available, `connectedDebugAndroidTest` also passes with eight destinations and working top-level navigation.

- [ ] **Step 7: Commit**

```bash
git add android
git commit -m "feat: scaffold Android navigation shell"
```

### Task 2: In-memory OpenAPI router and custom Volley transport

**Files:**
- Create: `android/app/src/main/java/cinelis/model/User.java`
- Create: `android/app/src/main/java/cinelis/model/Movie.java`
- Create: `android/app/src/main/java/cinelis/model/CinemaSession.java`
- Create: `android/app/src/main/java/cinelis/model/Seat.java`
- Create: `android/app/src/main/java/cinelis/model/Order.java`
- Create: `android/app/src/main/java/cinelis/model/Ticket.java`
- Create: `android/app/src/main/java/cinelis/network/ApiCallback.java`
- Create: `android/app/src/main/java/cinelis/network/ApiClient.java`
- Create: `android/app/src/main/java/cinelis/network/ApiError.java`
- Create: `android/app/src/main/java/cinelis/network/JsonModelMapper.java`
- Create: `android/app/src/main/java/cinelis/network/MockApiException.java`
- Create: `android/app/src/main/java/cinelis/network/MockApiResponse.java`
- Create: `android/app/src/main/java/cinelis/network/MockApiRouter.java`
- Create: `android/app/src/main/java/cinelis/network/MockApiStore.java`
- Create: `android/app/src/main/java/cinelis/network/MockNetwork.java`
- Create: `android/app/src/main/java/cinelis/network/MockRequestQueueFactory.java`
- Create: `android/app/src/test/java/cinelis/network/MockApiRouterTest.java`
- Create: `android/app/src/test/java/cinelis/network/JsonModelMapperTest.java`
- Modify: `android/app/src/main/java/cinelis/CineLisApplication.java`

**Interfaces:**
- Consumes: `PreferencesManager.getServerUrl()` from Task 1.
- Produces: `MockApiRouter.route(int method, String url, Map<String,String> headers, byte[] body): MockApiResponse`; `MockNetwork.performRequest(Request<?>): NetworkResponse`; singleton `ApiClient` methods for every OpenAPI operation; POJOs with IDs and display fields needed by the fragments.

- [ ] **Step 1: Write failing route and mapping tests**

Create named tests for login/logout/register, `GET|PUT|DELETE /auth/me`, movie list/search/genre/detail/sessions, session detail/seats, holds, order create/list/detail, and ticket list/detail. Assert representative OpenAPI status codes and required JSON keys. Add focused tests for unauthorized access, duplicate registration, missing resources, URL normalization with trailing slashes/query strings, hold conflict returning 409, and a newly constructed store returning seed data rather than prior mutations.

- [ ] **Step 2: Run unit tests to verify failure**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew testDebugUnitTest --tests 'cinelis.network.*'`

Expected: FAIL because the models/router/mapper do not exist.

- [ ] **Step 3: Implement immutable model objects and JSON mapping**

Give each model constructor/getters plus exact mapper entry points: `user(JSONObject)`, `movie(JSONObject)`, `session(JSONObject)`, `seat(JSONObject)`, `order(JSONObject)`, `ticket(JSONObject)`, and list helpers. Keep parsing independent of Views.

- [ ] **Step 4: Implement volatile seed store**

Seed the five existing `Catalog.php` movies, three dates with session times, two rooms, five-by-eight seat maps, occupied seats, demo customer `ana@exemplo.pt` / `password123`, three ticket examples, and matching orders. Use stable UUID-shaped IDs and process-memory collections only.

- [ ] **Step 5: Implement the OpenAPI router**

Dispatch method/path combinations listed in the spec, validate required bodies, parse bearer headers, filter movie/ticket queries, mutate holds/orders/account data, and translate failures to the OpenAPI `Error` or `ValidationError` shapes.

- [ ] **Step 6: Implement Volley transport and client**

Build a `RequestQueue(new NoCache(), new MockNetwork(router))`, start it once in `CineLisApplication`, and have `ApiClient` enqueue standard `JsonObjectRequest`/`JsonArrayRequest` objects. Add a deterministic 250 ms mock delay, request tags, cancellation, authorization headers, and typed callback mapping.

- [ ] **Step 7: Run network verification**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew testDebugUnitTest --tests 'cinelis.network.*'`

Expected: PASS for all route groups, error cases, mapping, URL normalization, conflicts, and reset behavior.

- [ ] **Step 8: Commit**

```bash
git add android/app/src/main android/app/src/test
git commit -m "feat: add in-app Volley API mock"
```

### Task 3: Home, movie/session detail, and authentication flow

**Files:**
- Create: `android/app/src/main/java/cinelis/ui/common/UiStateController.java`
- Create: `android/app/src/main/java/cinelis/ui/home/MovieAdapter.java`
- Create: `android/app/src/main/java/cinelis/ui/movie/SessionAdapter.java`
- Create: `android/app/src/main/res/layout/item_movie.xml`
- Create: `android/app/src/main/res/layout/item_upcoming_movie.xml`
- Create: `android/app/src/main/res/layout/item_session.xml`
- Create: `android/app/src/main/res/drawable/poster_last_light.xml`
- Create: `android/app/src/main/res/drawable/poster_between_tides.xml`
- Create: `android/app/src/main/res/drawable/poster_orbit.xml`
- Create: `android/app/src/main/res/drawable/poster_summer_days.xml`
- Create: `android/app/src/main/res/drawable/poster_other_side.xml`
- Create: `android/app/src/main/res/drawable/bg_card.xml`
- Create: `android/app/src/main/res/drawable/bg_input.xml`
- Create: `android/app/src/main/res/drawable/bg_button_primary.xml`
- Create: `android/app/src/main/res/drawable/bg_button_secondary.xml`
- Create: `android/app/src/androidTest/java/cinelis/CatalogAndAuthFlowTest.java`
- Modify: `android/app/src/main/java/cinelis/ui/home/HomeFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/movie/MovieDetailFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/auth/AuthFragment.java`
- Modify: `android/app/src/main/res/layout/fragment_home.xml`
- Modify: `android/app/src/main/res/layout/fragment_movie_detail.xml`
- Modify: `android/app/src/main/res/layout/fragment_auth.xml`
- Modify: `android/app/src/main/res/values/strings.xml`

**Interfaces:**
- Consumes: `ApiClient` catalogue/auth methods, `AppNavigator`, model POJOs, theme resources.
- Produces: dynamic AM-01/02/03 screens; `AuthFragment.ARG_RETURN_DESTINATION`; authenticated token held by `ApiClient` for the current process; search state controlled through `HomeFragment.focusSearch()`.

- [ ] **Step 1: Write the failing catalogue/auth instrumentation test**

Assert AM-01 Portuguese headings and seeded movies, search with no match shows an empty message and can be cleared, a movie opens AM-02 with three session times, protected session selection opens AM-03, invalid credentials show an inline error, registration mode validates email/password, successful login resumes the pending session, and a stale token/401 returns to login without crashing.

- [ ] **Step 2: Run the focused test to verify failure**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew connectedDebugAndroidTest -Pandroid.testInstrumentationRunnerArguments.class=cinelis.CatalogAndAuthFlowTest`

Expected: FAIL on missing dynamic controls/content.

- [ ] **Step 3: Implement shared loading/content/empty/error handling**

`UiStateController` accepts the four state containers and exposes `showLoading()`, `showContent()`, `showEmpty()`, and `showError(String, Runnable)`. Use it in Home and Movie Detail.

- [ ] **Step 4: Implement AM-01 Home**

Load `GET /movies`, render horizontal current/upcoming lists with abstract poster resources, search and genre filtering, the exact headline/copy, and empty/retry behavior. Route card/session actions through `AppNavigator`.

- [ ] **Step 5: Implement AM-02 Movie Detail**

Load movie and sessions, render metadata/synopsis/date tabs/session rows, keep the trailer button illustrative with a Snackbar, and gate seat selection on the process-local auth token.

- [ ] **Step 6: Implement AM-03 Login/Registration**

Build login as the default mockup state and toggle registration within the same fragment. Validate input, call Volley auth endpoints, retain pending navigation arguments, show inline errors, and resume the protected route after login.

- [ ] **Step 7: Run catalogue/auth verification**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew testDebugUnitTest connectedDebugAndroidTest assembleDebug lintDebug`

Expected: PASS, including empty search, invalid auth, stale token, and pending-route resumption.

- [ ] **Step 8: Commit**

```bash
git add android/app/src
git commit -m "feat: implement Android catalogue and authentication flow"
```

### Task 4: Seat selection and checkout flow

**Files:**
- Create: `android/app/src/main/java/cinelis/ui/seats/SeatGridAdapter.java`
- Create: `android/app/src/main/java/cinelis/ui/seats/SeatSelectionState.java`
- Create: `android/app/src/main/res/layout/item_seat.xml`
- Create: `android/app/src/main/res/drawable/bg_seat_available.xml`
- Create: `android/app/src/main/res/drawable/bg_seat_occupied.xml`
- Create: `android/app/src/main/res/drawable/bg_seat_selected.xml`
- Create: `android/app/src/main/res/drawable/bg_screen.xml`
- Create: `android/app/src/test/java/cinelis/ui/seats/SeatSelectionStateTest.java`
- Create: `android/app/src/androidTest/java/cinelis/PurchaseFlowTest.java`
- Modify: `android/app/src/main/java/cinelis/ui/seats/SeatSelectionFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/checkout/CheckoutFragment.java`
- Modify: `android/app/src/main/res/layout/fragment_seat_selection.xml`
- Modify: `android/app/src/main/res/layout/fragment_checkout.xml`
- Modify: `android/app/src/main/res/values/strings.xml`

**Interfaces:**
- Consumes: session/seats/holds/order `ApiClient` methods and navigation arguments from Task 3.
- Produces: `SeatSelectionState(List<Seat>, double price)`, `toggle(String seatId): boolean`, `selectedSeatIds(): ArrayList<String>`, `selectedLabels(): List<String>`, `total(): double`; AM-04/05 screens; ticket ID navigation result.

- [ ] **Step 1: Write failing selection-state tests**

Assert that available seats toggle, sold/held seats never select, duplicate toggles remove a seat, totals use 7.50 EUR per seat, and selected IDs/labels remain stable in row/number order.

- [ ] **Step 2: Write the failing purchase-flow instrumentation test**

Log in, open a session, assert the five-by-eight grid/legend, select A2/A3, verify `15,00 €`, continue, validate missing/invalid phone input, pay, and assert AM-06 shows the created ticket. Add a test that forces an unavailable hold response and verifies a Portuguese error plus disabled checkout.

- [ ] **Step 3: Run focused tests to verify failure**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew testDebugUnitTest --tests 'cinelis.ui.seats.*' connectedDebugAndroidTest -Pandroid.testInstrumentationRunnerArguments.class=cinelis.PurchaseFlowTest`

Expected: FAIL because selection behavior and AM-04/05 controls are absent.

- [ ] **Step 4: Implement AM-04 Seat Selection**

Load session and seats, render the three-step header, screen marker, five-by-eight grid, semantic seat states, legend, selection summary, total, and continue action. Enqueue the hold request only when at least one seat is selected; surface 409 without advancing.

- [ ] **Step 5: Implement AM-05 Checkout**

Render movie/date/time/room/seat summary, payment selector, phone field, notice, and total. Validate presentation-only payment input, enqueue `POST /orders`, clear the held-selection navigation state, and open the first returned ticket.

- [ ] **Step 6: Run purchase verification**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew testDebugUnitTest connectedDebugAndroidTest assembleDebug lintDebug`

Expected: PASS for selection logic, full purchase, input validation, hold conflicts, and ticket navigation.

- [ ] **Step 7: Commit**

```bash
git add android/app/src
git commit -m "feat: add Android seat and checkout flow"
```

### Task 5: Ticket detail, ticket lists, account/history, and settings

**Files:**
- Create: `android/app/src/main/java/cinelis/ui/tickets/TicketAdapter.java`
- Create: `android/app/src/main/java/cinelis/ui/account/OrderAdapter.java`
- Create: `android/app/src/main/java/cinelis/ui/account/AccountDialogs.java`
- Create: `android/app/src/main/res/layout/item_ticket.xml`
- Create: `android/app/src/main/res/layout/item_order.xml`
- Create: `android/app/src/main/res/layout/dialog_edit_account.xml`
- Create: `android/app/src/main/res/layout/dialog_server_settings.xml`
- Create: `android/app/src/main/res/layout/dialog_theme.xml`
- Create: `android/app/src/main/res/drawable/bg_ticket_code.xml`
- Create: `android/app/src/main/res/drawable/bg_avatar.xml`
- Create: `android/app/src/androidTest/java/cinelis/TicketsAndAccountTest.java`
- Modify: `android/app/src/main/java/cinelis/ui/ticket/TicketFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/tickets/TicketsFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/account/AccountFragment.java`
- Modify: `android/app/src/main/res/layout/fragment_ticket.xml`
- Modify: `android/app/src/main/res/layout/fragment_tickets.xml`
- Modify: `android/app/src/main/res/layout/fragment_account.xml`
- Modify: `android/app/src/main/res/values/strings.xml`

**Interfaces:**
- Consumes: ticket/order/account CRUD/logout `ApiClient` methods; `PreferencesManager` from Task 1.
- Produces: AM-06/07/08 screens; editable profile dialog; server URL dialog; system/light/dark theme dialog; logout and delete-account behavior.

- [ ] **Step 1: Write the failing tickets/account test**

Assert upcoming/previous ticket tabs render dynamic rows, empty data shows a Portuguese empty message, selecting a ticket opens AM-06, account shows user initials/email and order history, profile update changes displayed values, server URL survives activity recreation, theme selection recreates with correct night mode, logout returns to auth, and delete account clears the session.

- [ ] **Step 2: Run the test to verify failure**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew connectedDebugAndroidTest -Pandroid.testInstrumentationRunnerArguments.class=cinelis.TicketsAndAccountTest`

Expected: FAIL because AM-06/07/08 dynamic implementations and dialogs are absent.

- [ ] **Step 3: Implement AM-06 Ticket**

Load by `ticketId` when necessary, render success/title/session/seat/count/code treatment, keep download illustrative with a Snackbar, and provide loading/not-found handling.

- [ ] **Step 4: Implement AM-07 Tickets**

Load `upcoming=true|false`, render tabs and ticket cards, show content/empty/error states, and navigate to Ticket Detail.

- [ ] **Step 5: Implement AM-08 Account and history**

Load `/auth/me` and `/orders`, render initials/profile/history/logout, and implement styled edit-account, delete confirmation, theme, and server URL dialogs. Validate URL scheme/host locally; normalize one trailing slash in `PreferencesManager`.

- [ ] **Step 6: Run ticket/account verification**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew testDebugUnitTest connectedDebugAndroidTest assembleDebug lintDebug`

Expected: PASS for dynamic/empty states, profile CRUD, persistent preferences, theme recreation, logout, and account deletion.

- [ ] **Step 7: Commit**

```bash
git add android/app/src
git commit -m "feat: complete Android tickets and account screens"
```

### Task 6: Mockup fidelity, lifecycle hardening, and complete acceptance flow

**Files:**
- Create: `android/app/src/main/res/drawable/ic_home.xml`
- Create: `android/app/src/main/res/drawable/ic_search.xml`
- Create: `android/app/src/main/res/drawable/ic_ticket.xml`
- Create: `android/app/src/main/res/drawable/ic_account.xml`
- Create: `android/app/src/main/res/drawable/ic_arrow_forward.xml`
- Create: `android/app/src/main/res/drawable/ic_play.xml`
- Create: `android/app/src/main/res/drawable/ic_check.xml`
- Create: `android/app/src/main/res/drawable/cinelis_mark.xml`
- Create: `android/app/src/main/res/values/styles.xml`
- Create: `android/app/src/androidTest/java/cinelis/CompletePrototypeFlowTest.java`
- Modify: `android/app/src/main/java/cinelis/ui/home/HomeFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/movie/MovieDetailFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/auth/AuthFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/seats/SeatSelectionFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/checkout/CheckoutFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/ticket/TicketFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/tickets/TicketsFragment.java`
- Modify: `android/app/src/main/java/cinelis/ui/account/AccountFragment.java`
- Modify: `android/app/src/main/res/layout/activity_main.xml`
- Modify: `android/app/src/main/res/layout/fragment_home.xml`
- Modify: `android/app/src/main/res/layout/fragment_movie_detail.xml`
- Modify: `android/app/src/main/res/layout/fragment_auth.xml`
- Modify: `android/app/src/main/res/layout/fragment_seat_selection.xml`
- Modify: `android/app/src/main/res/layout/fragment_checkout.xml`
- Modify: `android/app/src/main/res/layout/fragment_ticket.xml`
- Modify: `android/app/src/main/res/layout/fragment_tickets.xml`
- Modify: `android/app/src/main/res/layout/fragment_account.xml`
- Modify: `README.md`
- Create: `android/README.md`

**Interfaces:**
- Consumes: all completed screen, navigation, Volley, and theme interfaces.
- Produces: polished mockup-aligned prototype, lifecycle-safe cancellation, project/run documentation, and final end-to-end acceptance coverage.

- [ ] **Step 1: Write the failing full-flow acceptance test**

From a fresh process, browse/search a movie, inspect sessions, authenticate, choose two seats, complete checkout, revisit the new ticket from My Tickets, inspect the new order in Account, switch theme, change server URL, log out, and confirm preferences remain while protected content returns to login.

- [ ] **Step 2: Run the acceptance test to expose remaining gaps**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew connectedDebugAndroidTest -Pandroid.testInstrumentationRunnerArguments.class=cinelis.CompletePrototypeFlowTest`

Expected: FAIL only on unpolished/missing cross-screen behavior discovered by the complete flow.

- [ ] **Step 3: Apply mockup fidelity pass**

Compare all eight screens in light and dark at 390 x 780 dp. Correct hierarchy, spacing, poster geometry, card/input/button shapes, bottom navigation selection, text wrapping, seat state symbols, and system insets. Add content descriptions and ensure every interactive control is at least 48 dp.

- [ ] **Step 4: Harden lifecycle and request cancellation**

Tag requests per fragment view, cancel them in `onDestroyView`, prevent callbacks from using cleared bindings, preserve form/selection state through rotation using `onSaveInstanceState`, and ensure rapid bottom-navigation changes cannot overwrite the current screen.

- [ ] **Step 5: Document the prototype boundary**

Add Android Studio/CLI build instructions, demo credentials, in-memory reset behavior, mocked endpoint list, theme/server settings, and explicit deferred SQLite/offline/real-API work to `android/README.md`; add `android/` to the repository structure in the root README.

- [ ] **Step 6: Run complete verification**

Run: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew clean testDebugUnitTest assembleDebug lintDebug`

Expected: `BUILD SUCCESSFUL`, all unit tests pass, debug APK is produced, and lint has no errors.

Run when an emulator/device is available: `cd android && JAVA_HOME=/opt/android-studio/jbr ./gradlew connectedDebugAndroidTest`

Expected: all navigation, catalogue/auth, purchase, ticket/account, and complete-flow instrumentation tests pass.

- [ ] **Step 7: Inspect repository state**

Run: `git status --short && git diff --check`

Expected: only intended Android/docs/root README changes and no whitespace errors.

- [ ] **Step 8: Commit**

```bash
git add android README.md docs/superpowers
git commit -m "feat: finish CineLis Android UI prototype"
```
