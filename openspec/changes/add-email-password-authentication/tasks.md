## 1. Install Laravel Breeze

- [x] 1.1 Install Laravel Breeze package via Composer: `composer require laravel/breeze --dev`
- [x] 1.2 Run Breeze installation with Blade stack: `php artisan breeze:install blade --no-interaction`
- [x] 1.3 Install npm dependencies: `npm install`
- [x] 1.4 Build frontend assets: `npm run build`
- [x] 1.5 Verify auth routes exist: `php artisan route:list --path=login`

## 2. Database Migration for User Ownership

- [x] 2.1 Create migration to add foreign key constraint on conversations.user_id
- [x] 2.2 Update migration to make user_id non-nullable with cascade delete
- [x] 2.3 Run migrations: `php artisan migrate`
- [x] 2.4 Verify database schema: check foreign key exists on conversations table

## 3. Update Models with Relationships

- [x] 3.1 Add `conversations()` relationship to User model: `return $this->hasMany(Conversation::class)`
- [x] 3.2 Add `user()` relationship to Conversation model: `return $this->belongsTo(User::class)`
- [x] 3.3 Add cascade delete to User model (optional): override `delete()` or use model events
- [x] 3.4 Update Conversation factory to create associated user: `user_id: User::factory()`
- [x] 3.5 Update User factory if needed for test scenarios

## 4. Update Actions to Scope by Authenticated User

- [x] 4.1 Update `ListConversationsAction` to filter by `auth()->id()`
- [x] 4.2 Update `GetConversationAction` to verify conversation ownership
- [x] 4.3 Update `CreateConversationAction` to set `user_id` from authenticated user
- [x] 4.4 Update `SendMessageAction` to validate conversation belongs to user
- [x] 4.5 Update `PrepareChatViewAction` to scope conversations to user
- [x] 4.6 Test each action with `php artisan test --filter=ActionTest`

## 5. Protect Routes with Authentication Middleware

- [x] 5.1 Wrap all chat routes in `auth` middleware group in routes/web.php
- [x] 5.2 Verify guest routes (login, register) are NOT protected
- [x] 5.3 Test unauthenticated access redirects to login: visit `/chat` without auth
- [x] 5.4 Test authenticated access works: `actingAs($user)->get('/chat')`
- [x] 5.5 Update welcome page to show Login/Register links for guests
- [x] 5.6 Update welcome page to show user menu for authenticated users

## 6. Configure Post-Login Redirect

- [x] 6.1 Update `.env` to set `HOME=/chat` or update RouteServiceProvider
- [x] 6.2 Test login redirects to `/chat` instead of `/dashboard`
- [x] 6.3 Test registration redirects to `/chat`
- [x] 6.4 (Optional) Customize dashboard view or remove if not needed

## 7. Update Tests for Authentication

- [x] 7.1 Update `ChatTest.php` to use `actingAs($user)` in all tests
- [x] 7.2 Update `CreateConversationActionTest.php` to authenticate user
- [x] 7.3 Update `GetConversationActionTest.php` to authenticate user
- [x] 7.4 Update `ListConversationsActionTest.php` to authenticate user
- [x] 7.5 Update `SendMessageActionTest.php` to authenticate user
- [x] 7.6 Update `ChatViewModelTest.php` to authenticate user
- [x] 7.7 Add test for unauthenticated access returns redirect
- [x] 7.8 Add test for user cannot access another user's conversation
- [x] 7.9 Add authentication feature tests (login, register, logout)
- [x] 7.10 Run full test suite: `php artisan test --compact`

## 8. Integration Testing and Verification

- [x] 8.1 Create test user via `/register` page
- [x] 8.2 Test login flow: verify redirect to `/chat`
- [x] 8.3 Test creating new conversation as authenticated user
- [x] 8.4 Test conversation list shows only user's conversations
- [x] 8.5 Test switching between conversations
- [x] 8.6 Test sending messages to conversation
- [x] 8.7 Test logout redirects to home page
- [x] 8.8 Test accessing `/chat` after logout redirects to login
- [x] 8.9 Create second user and verify conversation isolation
- [x] 8.10 Run Pint formatter: `vendor/bin/pint --format agent`

## 9. Documentation and Cleanup

- [ ] 9.1 Update AGENTS.md if needed with auth workflow notes
- [ ] 9.2 Review and update any outdated comments in Actions
- [ ] 9.3 Verify all routes are named correctly: `php artisan route:list`
- [ ] 9.4 Check browser logs for JavaScript errors during auth flow
- [ ] 9.5 Final test suite run: `php artisan test --compact` (all green)
