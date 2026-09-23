<?php
require_once __DIR__ . '/includes/config.php';

// Already logged in — go to dashboard
if (!empty($_SESSION['user'])) {
    redirect('/dashboard.php');
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Sign In — Digital Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Mono:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
/* ============================================================
   RESET & BASE
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --ink:        #0A0A0F;
  --ink-mid:    #1C1C2E;
  --ink-soft:   #2E2E45;
  --surface:    #F4F3EF;
  --surface-2:  #ECEAE3;
  --line:       #D4D1C7;
  --accent:     #1A3FFF;
  --accent-dim: #0D24B3;
  --gold:       #C8A84B;
  --gold-light: #E8CC7A;
  --success:    #0D7A4E;
  --error:      #C0392B;
  --error-bg:   #FDF0EE;
  --warn:       #8B5E00;

  --font-display: 'Syne', sans-serif;
  --font-mono:    'DM Mono', monospace;

  --ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
  --ease-spring:   cubic-bezier(0.34, 1.56, 0.64, 1);
}

html, body {
  height: 100%;
  font-family: var(--font-display);
  background: var(--surface);
  color: var(--ink);
  overflow: hidden;
}

/* ============================================================
   LAYOUT — SPLIT SCREEN
   ============================================================ */
.layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  height: 100vh;
}

/* ============================================================
   LEFT PANEL — BRAND
   ============================================================ */
.brand-panel {
  background: var(--ink);
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 3rem;
  overflow: hidden;
}

/* Animated grid background */
.brand-panel::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(26, 63, 255, 0.06) 1px, transparent 1px),
    linear-gradient(90deg, rgba(26, 63, 255, 0.06) 1px, transparent 1px);
  background-size: 48px 48px;
  animation: grid-drift 20s linear infinite;
}

@keyframes grid-drift {
  from { background-position: 0 0; }
  to   { background-position: 48px 48px; }
}

/* Glow orbs */
.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0;
  animation: orb-in 1.4s var(--ease-out-expo) forwards;
}
.orb-1 {
  width: 320px; height: 320px;
  background: radial-gradient(circle, rgba(26,63,255,0.35), transparent 70%);
  top: -60px; left: -60px;
  animation-delay: 0.2s;
}
.orb-2 {
  width: 240px; height: 240px;
  background: radial-gradient(circle, rgba(200,168,75,0.2), transparent 70%);
  bottom: 80px; right: -40px;
  animation-delay: 0.5s;
}

@keyframes orb-in {
  to { opacity: 1; }
}

/* Brand top */
.brand-top {
  position: relative;
  z-index: 1;
  opacity: 0;
  animation: fade-up 0.8s var(--ease-out-expo) 0.2s forwards;
}

.logo-mark {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 0;
}

.logo-icon {
  width: 40px; height: 40px;
  border: 1.5px solid rgba(26,63,255,0.6);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  background: rgba(26,63,255,0.08);
}

.logo-icon svg {
  width: 20px; height: 20px;
}

.logo-name {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.01em;
}

.logo-version {
  font-family: var(--font-mono);
  font-size: 10px;
  color: rgba(255,255,255,0.3);
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-top: 2px;
}

/* Brand center — hero copy */
.brand-center {
  position: relative;
  z-index: 1;
  opacity: 0;
  animation: fade-up 0.8s var(--ease-out-expo) 0.5s forwards;
}

.brand-eyebrow {
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--gold);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.brand-eyebrow::before {
  content: '';
  display: block;
  width: 24px; height: 1px;
  background: var(--gold);
}

.brand-headline {
  font-size: clamp(2.4rem, 3.5vw, 3.2rem);
  font-weight: 800;
  color: #fff;
  line-height: 1.08;
  letter-spacing: -0.03em;
  margin-bottom: 24px;
}

.brand-headline em {
  font-style: normal;
  color: var(--gold-light);
  position: relative;
}

.brand-body {
  font-size: 14px;
  color: rgba(255,255,255,0.45);
  line-height: 1.7;
  max-width: 340px;
  font-weight: 400;
}

/* Stats row */
.brand-stats {
  display: flex;
  gap: 32px;
  margin-top: 40px;
  padding-top: 32px;
  border-top: 1px solid rgba(255,255,255,0.07);
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-value {
  font-size: 22px;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.02em;
}

.stat-label {
  font-family: var(--font-mono);
  font-size: 10px;
  color: rgba(255,255,255,0.3);
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

/* Brand bottom */
.brand-bottom {
  position: relative;
  z-index: 1;
  opacity: 0;
  animation: fade-up 0.8s var(--ease-out-expo) 0.8s forwards;
}

.status-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: var(--font-mono);
  font-size: 11px;
  color: rgba(255,255,255,0.3);
}

.status-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: #22C55E;
  box-shadow: 0 0 8px #22C55E;
  animation: pulse-dot 2.4s ease-in-out infinite;
}

@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.4; }
}

/* ============================================================
   RIGHT PANEL — FORM
   ============================================================ */
.form-panel {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 3rem;
  background: var(--surface);
  position: relative;
}

.form-panel::before {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 1px; height: 100%;
  background: linear-gradient(to bottom, transparent, var(--line) 20%, var(--line) 80%, transparent);
}

.form-wrapper {
  width: 100%;
  max-width: 400px;
  opacity: 0;
  animation: fade-up 0.8s var(--ease-out-expo) 0.4s forwards;
}

/* Form header */
.form-header {
  margin-bottom: 40px;
}

.form-title {
  font-size: 28px;
  font-weight: 700;
  color: var(--ink);
  letter-spacing: -0.03em;
  line-height: 1.1;
  margin-bottom: 8px;
}

.form-subtitle {
  font-size: 13.5px;
  color: rgba(10,10,15,0.45);
  line-height: 1.5;
  font-weight: 400;
}

/* ============================================================
   FORM FIELDS
   ============================================================ */
.field-group {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 24px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

label {
  font-size: 12px;
  font-weight: 600;
  color: var(--ink-soft);
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.input-wrap {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(10,10,15,0.3);
  pointer-events: none;
  transition: color 0.2s;
}

input[type="text"],
input[type="password"],
input[type="email"] {
  width: 100%;
  height: 48px;
  padding: 0 44px;
  font-family: var(--font-display);
  font-size: 14px;
  font-weight: 500;
  color: var(--ink);
  background: #fff;
  border: 1.5px solid var(--line);
  border-radius: 10px;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  -webkit-appearance: none;
}

input::placeholder {
  color: rgba(10,10,15,0.25);
  font-weight: 400;
}

input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(26,63,255,0.08);
}

input:focus + .input-icon,
.input-wrap:focus-within .input-icon {
  color: var(--accent);
}

/* Password toggle */
.pwd-toggle {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: rgba(10,10,15,0.3);
  padding: 4px;
  transition: color 0.2s;
  display: flex;
  align-items: center;
}
.pwd-toggle:hover { color: var(--ink); }

/* Field error state */
.field.has-error input {
  border-color: var(--error);
  box-shadow: 0 0 0 3px rgba(192,57,43,0.08);
}

.field-error {
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--error);
  display: none;
}
.field.has-error .field-error { display: block; }

/* ============================================================
   MFA PANEL
   ============================================================ */
.mfa-panel {
  display: none;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 24px;
  padding: 20px;
  background: rgba(26,63,255,0.04);
  border: 1px solid rgba(26,63,255,0.15);
  border-radius: 12px;
}

.mfa-panel.visible { display: flex; }

.mfa-label {
  font-size: 13px;
  color: var(--ink-soft);
  line-height: 1.5;
}

.mfa-label strong { color: var(--ink); font-weight: 600; }

.mfa-input-row {
  display: flex;
  gap: 8px;
}

.mfa-digit {
  flex: 1;
  height: 52px;
  text-align: center;
  font-family: var(--font-mono);
  font-size: 20px;
  font-weight: 500;
  color: var(--ink);
  background: #fff;
  border: 1.5px solid var(--line);
  border-radius: 8px;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  -webkit-appearance: none;
}

.mfa-digit:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(26,63,255,0.08);
}

.mfa-digit.filled {
  border-color: var(--accent);
  background: rgba(26,63,255,0.03);
}

/* ============================================================
   FORM EXTRAS
   ============================================================ */
.form-extras {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.remember-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 13px;
  color: var(--ink-soft);
  font-weight: 400;
  user-select: none;
}

.remember-label input[type="checkbox"] {
  width: 16px; height: 16px;
  accent-color: var(--accent);
  cursor: pointer;
  padding: 0;
  flex-shrink: 0;
}

.forgot-link {
  font-size: 13px;
  color: var(--accent);
  text-decoration: none;
  font-weight: 500;
  transition: opacity 0.2s;
}
.forgot-link:hover { opacity: 0.7; }

/* ============================================================
   SUBMIT BUTTON
   ============================================================ */
.btn-submit {
  width: 100%;
  height: 50px;
  background: var(--ink);
  color: #fff;
  font-family: var(--font-display);
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.02em;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-submit:hover:not(:disabled) {
  background: var(--ink-soft);
  box-shadow: 0 4px 20px rgba(10,10,15,0.2);
  transform: translateY(-1px);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(0);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-submit.loading .btn-text { opacity: 0; }
.btn-submit.loading .btn-spinner { opacity: 1; }
.btn-submit .btn-spinner {
  position: absolute;
  opacity: 0;
  transition: opacity 0.2s;
}

/* Spinner ring */
.spinner-ring {
  width: 20px; height: 20px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Ripple on click */
.btn-submit .ripple {
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
  transform: scale(0);
  animation: ripple 0.5s linear;
  pointer-events: none;
}
@keyframes ripple {
  to { transform: scale(4); opacity: 0; }
}

/* ============================================================
   ALERT BANNER
   ============================================================ */
.alert {
  display: none;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 13px;
  line-height: 1.5;
  margin-bottom: 20px;
  animation: alert-in 0.3s var(--ease-out-expo);
}
.alert.visible { display: flex; }

@keyframes alert-in {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: translateY(0); }
}

.alert.error {
  background: var(--error-bg);
  border: 1px solid rgba(192,57,43,0.2);
  color: var(--error);
}

.alert.success {
  background: #EDF7F2;
  border: 1px solid rgba(13,122,78,0.2);
  color: var(--success);
}

.alert.warn {
  background: #FDF6E3;
  border: 1px solid rgba(139,94,0,0.2);
  color: var(--warn);
}

.alert-icon { flex-shrink: 0; margin-top: 1px; }

/* ============================================================
   DIVIDER
   ============================================================ */
.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 24px 0;
  color: rgba(10,10,15,0.25);
  font-size: 12px;
  font-family: var(--font-mono);
  letter-spacing: 0.06em;
}
.divider::before, .divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--line);
}

/* ============================================================
   SSO OPTION
   ============================================================ */
.btn-sso {
  width: 100%;
  height: 46px;
  background: transparent;
  color: var(--ink);
  font-family: var(--font-display);
  font-size: 13.5px;
  font-weight: 500;
  border: 1.5px solid var(--line);
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: border-color 0.2s, background 0.2s;
}
.btn-sso:hover {
  border-color: var(--ink);
  background: rgba(10,10,15,0.03);
}

/* ============================================================
   FOOTER NOTE
   ============================================================ */
.form-footer {
  margin-top: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.form-footer-text {
  font-family: var(--font-mono);
  font-size: 11px;
  color: rgba(10,10,15,0.3);
  letter-spacing: 0.04em;
}

.lock-icon {
  color: rgba(10,10,15,0.25);
}

/* ============================================================
   KEYFRAMES
   ============================================================ */
@keyframes fade-up {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 860px) {
  html, body { overflow: auto; }
  .layout {
    grid-template-columns: 1fr;
    height: auto;
    min-height: 100vh;
  }
  .brand-panel {
    min-height: 260px;
    padding: 2rem;
  }
  .brand-center { display: none; }
  .form-panel {
    padding: 2.5rem 1.5rem;
    align-items: stretch;
    justify-content: flex-start;
    padding-top: 3rem;
  }
  .form-panel::before { display: none; }
  .form-wrapper { max-width: 100%; }
}
</style>
</head>

<body>
<div class="layout" role="main">

  <!-- ====================================================
       LEFT — BRAND PANEL
       ==================================================== -->
  <aside class="brand-panel" aria-label="Digital Hub platform information">
    <div class="orb orb-1" aria-hidden="true"></div>
    <div class="orb orb-2" aria-hidden="true"></div>

    <div class="brand-top">
      <div class="logo-mark">
        <div class="logo-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 3L20 7.5V16.5L12 21L4 16.5V7.5L12 3Z" stroke="#1A3FFF" stroke-width="1.5" stroke-linejoin="round"/>
            <path d="M12 8L16 10.5V15.5L12 18L8 15.5V10.5L12 8Z" fill="rgba(26,63,255,0.2)" stroke="#1A3FFF" stroke-width="1"/>
          </svg>
        </div>
        <div>
          <div class="logo-name">Digital Hub</div>
          <div class="logo-version">v1.0 · Partner Portal</div>
        </div>
      </div>
    </div>

    <div class="brand-center">
      <div class="brand-eyebrow">Integration Platform</div>
      <h1 class="brand-headline">
        One hub.<br>Every <em>connection</em>.
      </h1>
      <p class="brand-body">
        Manage partners, control API access, and bridge integrations across CBS, Cosmos, billers, and ATM networks — all from a single pane of glass.
      </p>

      <div class="brand-stats">
        <div class="stat-item">
          <span class="stat-value">36+</span>
          <span class="stat-label">APIs</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">7</span>
          <span class="stat-label">Modules</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">99.9%</span>
          <span class="stat-label">Uptime</span>
        </div>
      </div>
    </div>

    <div class="brand-bottom">
      <div class="status-bar">
        <span class="status-dot" aria-hidden="true"></span>
        <span>All systems operational</span>
        <span style="margin-left:auto; font-size:10px;">© <?= date('Y') ?> Digital Hub</span>
      </div>
    </div>
  </aside>

  <!-- ====================================================
       RIGHT — FORM PANEL
       ==================================================== -->
  <section class="form-panel" aria-label="Sign in form">
    <div class="form-wrapper">

      <div class="form-header">
        <h2 class="form-title">Welcome back</h2>
        <p class="form-subtitle">Sign in to your partner portal account</p>
      </div>

      <!-- Alert banner -->
      <div class="alert" id="alert" role="alert" aria-live="polite">
        <span class="alert-icon" id="alert-icon" aria-hidden="true"></span>
        <span id="alert-msg"></span>
      </div>

      <!-- Login form -->
      <form id="login-form" novalidate autocomplete="off" aria-label="Sign in">
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="field-group">
          <!-- Username -->
          <div class="field" id="field-username">
            <label for="username">Username</label>
            <div class="input-wrap">
              <input
                type="text"
                id="username"
                name="username"
                placeholder="your.username"
                autocomplete="username"
                aria-required="true"
                aria-describedby="username-error"
                spellcheck="false"
              >
              <span class="input-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </span>
            </div>
            <span class="field-error" id="username-error" role="alert"></span>
          </div>

          <!-- Password -->
          <div class="field" id="field-password">
            <label for="password">Password</label>
            <div class="input-wrap">
              <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••••"
                autocomplete="current-password"
                aria-required="true"
                aria-describedby="password-error"
              >
              <span class="input-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
              </span>
              <button type="button" class="pwd-toggle" id="pwd-toggle" aria-label="Toggle password visibility" tabindex="-1">
                <svg id="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <svg id="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
            <span class="field-error" id="password-error" role="alert"></span>
          </div>
        </div>

        <!-- MFA panel (shown when API returns mfa_required) -->
        <div class="mfa-panel" id="mfa-panel" aria-hidden="true" aria-label="Multi-factor authentication">
          <div class="mfa-label">
            <strong>Two-factor authentication</strong><br>
            Enter the 6-digit code from your authenticator app.
          </div>
          <div class="mfa-input-row" role="group" aria-label="MFA code digits">
            <input class="mfa-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 1" autocomplete="off" data-idx="0">
            <input class="mfa-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 2" autocomplete="off" data-idx="1">
            <input class="mfa-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 3" autocomplete="off" data-idx="2">
            <input class="mfa-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 4" autocomplete="off" data-idx="3">
            <input class="mfa-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 5" autocomplete="off" data-idx="4">
            <input class="mfa-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 6" autocomplete="off" data-idx="5">
          </div>
        </div>

        <!-- Remember + forgot -->
        <div class="form-extras">
          <label class="remember-label">
            <input type="checkbox" id="remember" name="remember">
            Remember me
          </label>
          <a href="/forgot-password.php" class="forgot-link">Forgot password?</a>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit" id="btn-submit" aria-label="Sign in">
          <span class="btn-text">Sign in</span>
          <span class="btn-spinner" aria-hidden="true">
            <div class="spinner-ring"></div>
          </span>
        </button>
      </form>

      <!-- SSO divider -->
      <div class="divider" aria-hidden="true">or</div>
      <button type="button" class="btn-sso" id="btn-sso" aria-label="Sign in with SACCO SSO">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        Sign in with SACCO SSO
      </button>

      <!-- Secure note -->
      <div class="form-footer">
        <svg class="lock-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <span class="form-footer-text">Secured · TLS 1.3 · Session-bound auth</span>
      </div>

    </div>
  </section>
</div>

<script>
'use strict';

// ============================================================
// STATE
// ============================================================
const state = {
  mfaRequired:  false,
  mfaChallenge: null,
  submitting:   false,
};

// ============================================================
// ELEMENTS
// ============================================================
const form      = document.getElementById('login-form');
const btnSubmit = document.getElementById('btn-submit');
const alertEl   = document.getElementById('alert');
const alertMsg  = document.getElementById('alert-msg');
const alertIcon = document.getElementById('alert-icon');
const mfaPanel  = document.getElementById('mfa-panel');
const mfaDigits = document.querySelectorAll('.mfa-digit');
const pwdInput  = document.getElementById('password');
const pwdToggle = document.getElementById('pwd-toggle');
const eyeShow   = document.getElementById('eye-show');
const eyeHide   = document.getElementById('eye-hide');

// ============================================================
// UTILS
// ============================================================
function showAlert(msg, type = 'error') {
  const icons = {
    error:   '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    success: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
    warn:    '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
  };
  alertEl.className = 'alert ' + type + ' visible';
  alertIcon.innerHTML = icons[type] || icons.error;
  alertMsg.textContent = msg;
}

function hideAlert() {
  alertEl.className = 'alert';
  alertMsg.textContent = '';
}

function setFieldError(fieldId, msg) {
  const field = document.getElementById('field-' + fieldId);
  const err   = document.getElementById(fieldId + '-error');
  if (field) field.classList.toggle('has-error', !!msg);
  if (err)   err.textContent = msg || '';
}

function clearFieldErrors() {
  document.querySelectorAll('.field').forEach(f => f.classList.remove('has-error'));
  document.querySelectorAll('.field-error').forEach(e => e.textContent = '');
}

function setLoading(loading) {
  state.submitting = loading;
  btnSubmit.disabled = loading;
  btnSubmit.classList.toggle('loading', loading);
}

function addRipple(e) {
  const btn  = e.currentTarget;
  const rect = btn.getBoundingClientRect();
  const r = document.createElement('span');
  r.className = 'ripple';
  const size = Math.max(btn.clientWidth, btn.clientHeight);
  r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - rect.left - size/2}px;top:${e.clientY - rect.top - size/2}px`;
  btn.appendChild(r);
  r.addEventListener('animationend', () => r.remove());
}

// ============================================================
// PASSWORD TOGGLE
// ============================================================
pwdToggle.addEventListener('click', () => {
  const isText = pwdInput.type === 'text';
  pwdInput.type = isText ? 'password' : 'text';
  eyeShow.style.display = isText ? 'block' : 'none';
  eyeHide.style.display = isText ? 'none'  : 'block';
  pwdInput.focus();
});

// ============================================================
// MFA DIGIT INPUTS
// ============================================================
mfaDigits.forEach((digit, i) => {
  digit.addEventListener('input', e => {
    const val = e.target.value.replace(/\D/g, '');
    e.target.value = val;
    e.target.classList.toggle('filled', val.length > 0);
    if (val && i < mfaDigits.length - 1) mfaDigits[i + 1].focus();
    if (getMfaCode().length === 6) btnSubmit.focus();
  });

  digit.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !e.target.value && i > 0) {
      mfaDigits[i - 1].value = '';
      mfaDigits[i - 1].classList.remove('filled');
      mfaDigits[i - 1].focus();
    }
    if (e.key === 'ArrowLeft' && i > 0) mfaDigits[i - 1].focus();
    if (e.key === 'ArrowRight' && i < mfaDigits.length - 1) mfaDigits[i + 1].focus();
  });

  digit.addEventListener('paste', e => {
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
    text.split('').forEach((ch, j) => {
      if (mfaDigits[j]) {
        mfaDigits[j].value = ch;
        mfaDigits[j].classList.add('filled');
      }
    });
    const next = Math.min(text.length, mfaDigits.length - 1);
    mfaDigits[next].focus();
  });
});

function getMfaCode() {
  return Array.from(mfaDigits).map(d => d.value).join('');
}

function showMfaPanel() {
  state.mfaRequired = true;
  mfaPanel.classList.add('visible');
  mfaPanel.setAttribute('aria-hidden', 'false');
  // Disable the credential fields
  document.getElementById('username').disabled = true;
  document.getElementById('password').disabled = true;
  mfaDigits[0].focus();
}

// ============================================================
// VALIDATION
// ============================================================
function validate() {
  let valid = true;
  clearFieldErrors();
  hideAlert();

  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;

  if (!state.mfaRequired) {
    if (!username) {
      setFieldError('username', 'Username is required.');
      valid = false;
    }
    if (!password) {
      setFieldError('password', 'Password is required.');
      valid = false;
    } else if (password.length < 6) {
      setFieldError('password', 'Password must be at least 6 characters.');
      valid = false;
    }
  } else {
    if (getMfaCode().length < 6) {
      showAlert('Please enter all 6 digits of your authentication code.', 'warn');
      valid = false;
    }
  }
  return valid;
}

// ============================================================
// FORM SUBMIT
// ============================================================
btnSubmit.addEventListener('click', addRipple);

form.addEventListener('submit', async e => {
  e.preventDefault();
  if (state.submitting) return;
  if (!validate()) return;

  setLoading(true);
  hideAlert();

  const payload = {
    action:     'login',
    username:   document.getElementById('username').value.trim(),
    password:   document.getElementById('password').value,
    mfa_code:   state.mfaRequired ? getMfaCode() : '',
    csrf_token: document.getElementById('csrf_token').value,
  };

  try {
    const res  = await fetch('/auth.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body:    JSON.stringify(payload),
      credentials: 'same-origin',
    });

    const data = await res.json();

    if (data.success) {
      if (data.data?.mfa_required) {
        // Show MFA panel
        showMfaPanel();
        showAlert('Authentication code required. Check your authenticator app.', 'warn');
        setLoading(false);
        return;
      }

      // Success — redirect
      showAlert('Login successful. Redirecting…', 'success');
      setTimeout(() => {
        window.location.href = data.data?.redirect || '/dashboard.php';
      }, 600);
      return;
    }

    // Error handling by HTTP code
    switch (res.status) {
      case 401:
      case 403:
        showAlert(data.message || 'Invalid username or password.', 'error');
        // Shake the form
        document.querySelector('.form-wrapper').style.animation = 'none';
        void document.querySelector('.form-wrapper').offsetHeight;
        document.querySelector('.form-wrapper').style.animation = '';
        // Clear password
        document.getElementById('password').value = '';
        document.getElementById('password').focus();
        break;
      case 423:
        showAlert('This account has been locked due to too many failed attempts. Please contact your administrator.', 'error');
        btnSubmit.disabled = true;
        break;
      case 429:
        showAlert(data.message || 'Too many attempts. Please wait before trying again.', 'warn');
        break;
      default:
        showAlert(data.message || 'An unexpected error occurred. Please try again.', 'error');
    }

    setLoading(false);

  } catch (err) {
    console.error('Login error:', err);
    showAlert('Unable to connect. Please check your connection and try again.', 'error');
    setLoading(false);
  }
});

// ============================================================
// SSO BUTTON
// ============================================================
document.getElementById('btn-sso').addEventListener('click', () => {
  window.location.href = '/sso-redirect.php';
});

// ============================================================
// KEYBOARD SHORTCUT — focus username on page load
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('username').focus();
});

// ============================================================
// Auto-fill detection — remove autofill background
// ============================================================
document.querySelectorAll('input').forEach(inp => {
  inp.addEventListener('animationstart', e => {
    if (e.animationName === 'onAutoFillStart') inp.classList.add('auto-filled');
  });
});
</script>

<!-- Auto-fill style override -->
<style>
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-box-shadow: 0 0 0 40px #fff inset !important;
  -webkit-text-fill-color: var(--ink) !important;
  transition: background-color 5000s ease-in-out 0s;
}
</style>

</body>
</html>
