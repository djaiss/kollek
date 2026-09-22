---
name: take-screenshot
description: Use this skill whenever the task requires taking, inspecting, or validating screenshots of a website or web application. Screenshots must be taken with Iris.
---

# Screenshot skill instructions

Use [Iris](https://github.com/brijr/iris) for all website screenshots and visual
UI verification.

Iris is the camera. Do not introduce Playwright, Puppeteer, Selenium, or custom
browser automation solely to take screenshots.

## The host

Whoever installs the project chooses the host it is served under, so never write one
into a command. Read it from the environment and let the shell fill it in:

```bash
APP_URL=$(grep '^APP_URL=' .env | cut -d '=' -f2- | tr -d '"')
```

Every example below writes `"$APP_URL"` for that reason. Keep the quotes: the value
carries a scheme and may carry a port.

## Taking screenshots

For a standard desktop screenshot:

```bash
iris "$APP_URL" --scale 1 -o /tmp/screenshot.png
```

For a full-page screenshot:

```bash
iris --full "$APP_URL" --scale 1 -o /tmp/screenshot.png
```

For a specific element:

```bash
iris "$APP_URL" \
  --selector '#target' \
  --padding 24 \
  --scale 1 \
  -o /tmp/screenshot.png
```

For mobile:

```bash
iris --size iphone "$APP_URL" --scale 1 -o /tmp/screenshot.png
```

For dark mode:

```bash
iris --dark "$APP_URL" --scale 1 -o /tmp/screenshot.png
```

If the page depends on asynchronously rendered content, wait for a meaningful
element instead of adding arbitrary delays:

```bash
iris "$APP_URL" \
  --wait-for '[data-page-ready]' \
  --scale 1 \
  -o /tmp/screenshot.png
```

Use `--wait` only when waiting for a selector is not sufficient.

## A screen behind the sign in

Iris opens a URL and nothing else. It carries no cookie and no header, and each capture starts from
a fresh browser, so signing in and then capturing does not work: the session does not survive from
one capture to the next.

There is no way around that today. The project signs a developer in through
`spatie/laravel-login-link`, the button rendered by `<x-login-link>` on the sign in screen, and that
posts a form to `laravel-login-link-login`. Iris only issues a GET, so it cannot press it, and no
command mints a link that signs somebody in from a URL.

So capture what is served to a signed out visitor: the marketing site, the documentation portal, the
sign in and registration screens, the error pages. For a screen behind the sign in, say so in the
pull request and describe the change in a bullet instead of attaching a shot of it.

Giving Iris a signed in screen would mean adding a signed, development only GET route that logs a
user in and redirects. Nobody has written one. Do not improvise one to get a screenshot.

## Screens that scroll

The application shell fills the window and scrolls inside itself, so `--full` returns the viewport
rather than the whole screen. Ask for a viewport tall enough to hold what you want instead:

```bash
iris --scale 1 --size 1440x1600 '<the link>' -o /tmp/screen.png
```

Turn the debug bar off first, or it sits across the bottom of every capture:

```dotenv
DEBUGBAR_ENABLED=false
```

## Workflow

When visual validation is required:

1. Make sure the application is running.
2. Take the smallest screenshot that adequately validates the work.
3. Prefer an element screenshot when checking a specific component.
4. Prefer a normal viewport screenshot when checking page composition.
5. Use `--full` only when the entire page matters.
6. Open and inspect the resulting image.
7. Compare what is visible against the requested design or expected behavior.
8. If something is wrong, make the necessary changes and capture a new
   screenshot.
9. Continue until the screenshot confirms the requested result.

Do not claim that a visual change works without inspecting the screenshot.

## Screenshot sizes

Use these Iris presets when appropriate:

* `desktop` for the standard desktop view.
* `iphone` for the mobile phone view.
* `ipad` for the tablet view.

Use explicit dimensions when the task requires a particular viewport:

```bash
iris --size 1280x720 "$APP_URL" --scale 1 -o /tmp/screenshot.png
```

Default to `--scale 1` for agent verification because it keeps screenshots
smaller while preserving enough detail for UI review. Use a higher scale only
when additional pixel density is useful.

## Output

Store temporary screenshots outside the repository unless the user explicitly
asks for them to be committed or saved:

```text
/tmp/screenshot.png
/tmp/screenshot-mobile.png
/tmp/screenshot-dark.png
```

Use descriptive names when taking multiple screenshots.

Do not add generated screenshots to Git or store them anywhere in the
repository.

Screenshots taken for a pull request stay in `/tmp` and are uploaded directly
to the pull request with GitHub CLI, as the
[pull requests skill](../pull-requests/SKILL.md) describes.

## Useful Iris options

```text
--full             Capture the full page
--selector <CSS>   Capture the first matching element
--padding <PX>     Add padding around an element capture
--size <SIZE>      desktop, iphone, ipad, or WxH
--dark             Emulate prefers-color-scheme: dark
--wait-for <CSS>   Wait until an element exists
--wait <MS>        Additional settle time
--scale <N>        Device scale factor
--timeout <SECS>   Capture timeout
--json             Return structured capture information
-o, --out <PATH>   Output path
```

When debugging a failed capture, use `--json`:

```bash
iris "$APP_URL" \
  --scale 1 \
  --json \
  -o /tmp/screenshot.png
```

Inspect the reported status rather than assuming the image was successfully
created.

## Rules

* Always use Iris for screenshots.
* Do not use browser automation just to capture an image.
* Do not use screenshots as a substitute for automated functional tests.
* Do use screenshots to verify layout, spacing, typography, responsive
  behavior, visual regressions, and the final appearance of UI changes.
* Prefer targeted screenshots over unnecessarily large full-page captures.
* Always inspect the resulting image before drawing conclusions from it.
