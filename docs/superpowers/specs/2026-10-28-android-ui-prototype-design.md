# CineLis Android UI Prototype Design

## Intent

Add an `android/` Android Studio project to the CineLis repository so the team can evaluate the client application's navigation, visual language, and purchase flow before the production API and offline features are implemented. The prototype is for visitors and registered cinema customers. It should feel like the existing CineLis frontoffice and match the eight Android frames in `cinelis-mobile-mockups.excalidraw`, including their light and dark appearances.

The user's direct request is authoritative. The assignment brief and project report provide requirements and context, but they do not expand this iteration beyond the requested UI prototype.

## Goals

- Create an Android Studio project under repository-root `android/` using Java and XML Views, based on an Empty Views Activity structure.
- Implement one activity and eight navigable fragments matching AM-01 through AM-08:
  1. home/movie catalogue;
  2. movie details and sessions;
  3. login/registration;
  4. seat selection;
  5. checkout;
  6. ticket confirmation/detail;
  7. tickets;
  8. account and purchase history.
- Reproduce the mockups' CineLis styling: warm neutral surfaces, teal actions, compact radii, abstract poster artwork, bottom navigation, light/dark themes, Portuguese copy, and the documented spacing/type hierarchy.
- Make the primary flow usable end to end: browse -> select a movie/session -> authenticate if needed -> select seats -> review/pay -> view ticket -> revisit tickets/history.
- Use Volley asynchronously through an in-app custom `Network` transport. The UI should issue normal Volley JSON requests while the transport routes them to an in-memory mock backend instead of the network.
- Simulate the REST operations in `docs/openapi.yaml`, including authentication/account CRUD, catalogue/session reads, seat holds, order creation/history, and ticket reads.
- Keep the project buildable without requiring the Yii applications or a running web server.

## Non-goals

- No calls to the real CineLis API and no production HTTP service implementation.
- No SQLite, Room, cache, offline mode, or durable mock-server data in this iteration. SQLite remains required by the final assignment, but is intentionally deferred to the future offline feature requested by the user.
- No real payments, QR/barcode validity, trailer playback, ticket downloading, push/messaging, or production authentication/security.
- No Android backoffice screens. The backoffice mockups are a style/domain reference only; the mobile app remains customer-facing.
- No tablet-specific redesign. Layouts should remain usable on ordinary phones, with 390 x 780 dp as the visual reference.

## Constraints and source material

- Java source only; no Kotlin source or Jetpack Compose.
- XML layouts and standard Android Views.
- One `MainActivity` created in the shape of Android Studio's Empty Views Activity template, with fragments hosted inside it.
- Eight fragments satisfy the assignment's six-to-ten Activity/Fragment requirement and its requirement that some functionality use fragments.
- Volley must remain visible in the request path even though responses are generated in process.
- The mock service is volatile: it is seeded whenever the application process starts, and mutations survive only for that process lifetime.
- SharedPreferences may store client preferences such as selected theme and the editable server URL. It must not be used as a substitute for the deferred SQLite/offline feature.
- The account screen exposes a settings action for the API base URL, satisfying the assignment's configurable-server UI even though the mock transport intercepts requests locally.
- `docs/openapi.yaml` is the contract. Its current filename is `.yaml`, despite the request referring to `.yml`.
- UI text is Portuguese, matching the supplied mockups and existing frontoffice.

## Approaches considered

### Selected: custom Volley `Network` transport

Use standard Volley requests and a `RequestQueue` backed by a custom `Network` implementation. The network implementation passes method, URL, headers, and request body to an in-process router, which returns a Volley `NetworkResponse`. This keeps asynchronous queueing, parsing, cancellation, error handling, and future migration to real Volley HTTP behavior without opening a socket.

This is the simplest option that still genuinely exercises Volley and preserves the API boundary.

### Rejected: custom mock-only Volley request classes

Each request could return fixture data directly from `parseNetworkResponse`. This is slightly shorter initially, but it couples controllers to mock-specific request types and makes replacement with normal HTTP requests harder.

### Rejected: embedded localhost HTTP server

An embedded server would simulate HTTP most literally, but introduces port, lifecycle, and threading complexity without improving the prototype's UI/flow objective.

## Application structure

The application follows a lightweight MVC-style split suitable for the course:

- `model/`: immutable or simple Java data objects for users, movies, sessions, seats, orders, and tickets.
- `network/`: Volley queue creation, normal JSON request methods, the custom mock network, router, response helpers, and volatile mock store.
- `controller/`: fragment classes coordinate requests, validation, navigation, and view state.
- `view/`: XML layouts, RecyclerView adapters/view holders, custom seat-map view, theme resources, drawables, strings, dimensions, and menus.
- `storage/PreferencesManager`: theme and server URL only.

`MainActivity` owns the app shell, the fragment host, bottom navigation, and route helpers. A navigation graph defines the eight fragments and typed arguments are kept deliberately small (IDs and selected seat IDs). Process-local mock data remains the source of truth; fragments reload from the mock API rather than passing complete objects.

## Screens and navigation

### AM-01: Home / Movies

- CineLis wordmark, language marker, headline, search field, “Em exibição” horizontal cards, and “Em breve” card.
- Search filters the mock `GET /movies` result by title; genre chips provide the optional genre query.
- Selecting a movie opens AM-02.

### AM-02: Movie + Sessions

- Poster treatment, title, age rating, duration, genre, trailer affordance, synopsis, date tabs, and session rows.
- Uses the master/detail operation `GET /movies/{movieId}/sessions`.
- Selecting a session opens AM-04 if authenticated or AM-03 with the intended destination retained.

### AM-03: Login / Registration

- Login is the initial mode and visually follows the supplied frame.
- A secondary action switches the same fragment into a registration form, providing a second user-input flow without adding a ninth screen.
- Successful authentication resumes the pending seat-selection route; otherwise it returns to the account destination.
- Errors are inline and expressed in plain Portuguese.

### AM-04: Seat Selection

- Movie/session summary, three-step indicator, screen marker, five-by-eight seat grid, legend, selected-seat summary, and total.
- Seat buttons visibly distinguish available, held/sold, and selected states with both colour and symbols.
- The map loads with `GET /sessions/{sessionId}/seats`; continuing calls `POST /sessions/{sessionId}/holds` and opens AM-05.

### AM-05: Checkout

- Purchase summary, payment-method selector, phone-number input, total, and primary payment action.
- Payment input is illustrative and locally validated. The primary action calls `POST /orders` and opens the first resulting ticket in AM-06.

### AM-06: Ticket

- Success state, movie/session information, prominent seat identifier, ticket count position, and illustrative machine-readable code treatment.
- Uses `GET /tickets/{ticketId}` when opened from the tickets list; can render the just-created order response immediately while refreshing through the same endpoint.

### AM-07: My Tickets

- Upcoming/previous tabs and dynamic ticket cards.
- Uses `GET /tickets?upcoming=true|false`; selecting a row opens AM-06.

### AM-08: History / Account

- User summary, initials avatar, dynamic order history, logout, edit-account affordance, theme choice, and settings action for the server URL.
- Uses `GET /auth/me`, `GET /orders`, `PUT /auth/me`, `DELETE /auth/me`, and `POST /auth/logout` as appropriate.
- Account editing and server settings use styled dialogs so the project remains within eight fragments.

The bottom navigation exposes Home, Search, Tickets, and Account. Search focuses/opens the catalogue search state rather than creating an extra fragment. The bottom bar remains present on top-level destinations and may remain visible on the flow screens to match the mockups.

## Volley mock API design

`ApiClient` constructs ordinary `JsonObjectRequest` or `JsonArrayRequest` objects using the configured base URL. `MockRequestQueueFactory` creates a Volley `RequestQueue` with `NoCache` and `MockNetwork`. `MockNetwork.performRequest()` delegates to `MockApiRouter`, converts the router result to UTF-8 JSON bytes, and returns a `NetworkResponse` after a small deterministic delay so loading states are visible.

The router normalizes the URL to an OpenAPI path and dispatches on HTTP method and path. It returns realistic status codes and JSON bodies, including validation, unauthorized, conflict, and not-found errors. Bearer tokens are process-local opaque strings. The initial seed includes the mockup's films, dates, rooms, seats, demo customer, orders, and tickets. State-changing requests mutate only `MockApiStore` memory.

The routes covered are:

- `POST /auth/login`
- `POST /auth/logout`
- `POST /auth/register`
- `GET`, `PUT`, `DELETE /auth/me`
- `GET /movies`
- `GET /movies/{movieId}`
- `GET /movies/{movieId}/sessions`
- `GET /sessions/{sessionId}`
- `GET /sessions/{sessionId}/seats`
- `POST /sessions/{sessionId}/holds`
- `POST`, `GET /orders`
- `GET /orders/{orderId}`
- `GET /tickets`
- `GET /tickets/{ticketId}`

Requests are cancelled by fragment/view lifecycle tag when a view is destroyed. Loading, empty, content, and error states are explicit in dynamic screens.

## Visual system

- Light: background `#F6F5F4`, foreground `#161512`, cards `#EDEBE9`, primary `#0F766E`, secondary `#E3E1DD`, muted `#F1F0EE`, muted text `#625C50`, border `#D6D3CD`.
- Dark: background `#161512`, foreground `#F1F0EE`, cards `#22201C`, primary `#5EEAD4`, secondary `#2D2A25`, muted `#1C1A17`, muted text `#B6B0A5`, border `#433F37`.
- Semantic accents: success `#4CF090`, warning `#F0994C`, danger `#F0594C`.
- Spacing follows a 4 dp base scale: 4, 8, 12, 16, 20, 24, 32, and 40 dp.
- Radii: 4 dp fields, 6 dp buttons, and 8 dp cards. Primary mobile actions are at least 48 dp high.
- Android's sans-serif family is used as the no-extra-binary dependency approximation of Inter. Weight, size, casing, and letter spacing reproduce the mockup hierarchy.
- Abstract poster art is implemented as repository-native vector/shape drawables rather than external imagery. The CineLis mark/wordmark is recreated as Android vector/text treatment from the existing brand assets.
- Day/night resource qualifiers provide both themes, and the account settings allow system, light, or dark selection.

## State and behavior

- Authentication and backend mutations are process-local and reset with the application process.
- Theme and server URL preferences persist with SharedPreferences.
- A successful order changes selected held seats to sold, creates tickets, and makes them appear in ticket and history screens during the current process.
- Protected routes return a mock 401 when no token is supplied. Controllers redirect to login while retaining the intended destination.
- Back navigation follows the purchase flow naturally and bottom-navigation choices return to top-level destinations.

## Validation and errors

- Email syntax and minimum eight-character password checks mirror the OpenAPI schema.
- Registration prevents duplicate email addresses for the current process.
- Seat holds reject unavailable seats with 409.
- Orders reject missing/expired holds and empty selections.
- Forms show inline errors; request-level failures show a compact retry state or Snackbar.
- The payment phone number is presentation-only and is never persisted.

## Testing and acceptance

- JVM unit tests exercise router success/error cases for every route group, including account CRUD, authentication, filtering, holds, order creation, and protected reads.
- JVM tests verify mock mutations: registration/login, profile update/delete, hold conflict, purchase, and ticket/history visibility.
- Android instrumentation smoke tests launch the activity, verify all eight destinations can render, and complete the main purchase flow using the in-app mock.
- Gradle build, Java unit tests, lint, and available instrumentation tests must pass before completion is claimed.
- Manual visual review compares representative light and dark screens at the 390 x 780 dp mockup size.

## Builder decisions left open

- Select stable Android Gradle Plugin and dependency versions compatible with the Android SDK already installed on the machine.
- Choose exact adapter/view-binding helpers while keeping Java/XML Views and the architecture above.
- Make minor responsive spacing adjustments where Android system insets differ from the Excalidraw canvas.
