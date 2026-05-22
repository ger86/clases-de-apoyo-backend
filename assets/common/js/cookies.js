const COOKIE_NAME = 'cda_cookie_preferences';
const COOKIE_MAX_AGE_SECONDS = 180 * 24 * 60 * 60; // 6 months

const readCookie = (name) => {
  const pattern = '(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)';
  const match = document.cookie.match(new RegExp(pattern));

  return match ? decodeURIComponent(match[1]) : null;
};

const writeCookie = (name, value, maxAgeSeconds) => {
  let cookie = `${name}=${encodeURIComponent(value)}; path=/; samesite=lax`;

  if (typeof maxAgeSeconds === 'number') {
    cookie += `; max-age=${maxAgeSeconds}`;
  }

  if (window.location.protocol === 'https:') {
    cookie += '; secure';
  }

  document.cookie = cookie;
};

const deleteCookie = (name) => {
  document.cookie = `${name}=; path=/; max-age=0; samesite=lax`;
};

const cookies = {
  state: {
    analyticsLoaded: false,
    adsLoaded: false,
    adsConfigured: false,
  },

  init() {
    if (typeof document === 'undefined') {
      return;
    }

    const onReady = () => this.handleReady();

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', onReady, { once: true });
    } else {
      onReady();
    }
  },

  handleReady() {
    this.banner = document.querySelector('[data-cookie-banner]');
    this.analyticsToggle = this.banner ? this.banner.querySelector('[data-cookie-toggle="analytics"]') : null;
    this.adsToggle = this.banner ? this.banner.querySelector('[data-cookie-toggle="ads"]') : null;
    this.acceptButton = this.banner ? this.banner.querySelector('[data-cookie-accept]') : null;
    this.saveButton = this.banner ? this.banner.querySelector('[data-cookie-save]') : null;
    this.rejectButton = this.banner ? this.banner.querySelector('[data-cookie-reject]') : null;
    this.closeButton = this.banner ? this.banner.querySelector('[data-cookie-close]') : null;
    this.openButtons = Array.from(document.querySelectorAll('[data-cookie-open]'));

    const bodyDataset = document.body ? document.body.dataset : {};

    this.gaId = bodyDataset ? bodyDataset.gaId : '';
    this.gaEnabled = Boolean(bodyDataset && bodyDataset.gaEnabled === '1' && this.gaId);
    this.adsClientId = bodyDataset ? bodyDataset.googleAdsClient : '';
    this.adsEnabled = Boolean(bodyDataset && bodyDataset.googleAdsEnabled === '1' && this.adsClientId);

    this.storedPreferences = this.getPreferences();
    this.currentPreferences = this.storedPreferences
      ? { ...this.storedPreferences }
      : this.normalizePreferences({ analytics: false, ads: false });

    if (!this.gaEnabled || !this.currentPreferences.analytics) {
      this.setGaDisableFlag(true);
    }

    if (this.banner) {
      if (this.storedPreferences) {
        this.syncUIWithPreferences(this.currentPreferences);
        this.hideBanner();
      } else {
        this.resetToggles();
        this.showBanner();
      }
    }

    this.applyPreferences(this.currentPreferences, { persist: false, syncUI: false });
    this.updateCloseButtonVisibility();
    this.bindEvents();
    this.attachOpenButtons();
  },

  getPreferences() {
    const raw = readCookie(COOKIE_NAME);

    if (!raw) {
      return null;
    }

    try {
      const parsed = JSON.parse(raw);
      return this.normalizePreferences(parsed);
    } catch (error) {
      deleteCookie(COOKIE_NAME);
      return null;
    }
  },

  normalizePreferences(preferences = {}) {
    const normalized = {
      analytics: Boolean(preferences.analytics && this.gaEnabled),
      ads: Boolean(preferences.ads && this.adsEnabled),
    };

    return normalized;
  },

  persistPreferences(preferences) {
    writeCookie(COOKIE_NAME, JSON.stringify(preferences), COOKIE_MAX_AGE_SECONDS);
  },

  applyPreferences(preferences, options = {}) {
    const { persist = false, syncUI = false } = options;
    const normalized = this.normalizePreferences(preferences);

    this.currentPreferences = { ...normalized };

    if (syncUI) {
      this.syncUIWithPreferences(normalized);
    }

    if (this.gaEnabled) {
      if (normalized.analytics) {
        this.enableAnalytics();
      } else {
        this.disableAnalytics();
      }
    } else {
      this.disableAnalytics();
    }

    if (this.adsEnabled) {
      if (normalized.ads) {
        this.enableAds();
      } else {
        this.disableAds();
      }
    } else {
      this.disableAds();
    }

    if (persist) {
      this.persistPreferences(normalized);
      this.storedPreferences = { ...normalized };
    }

    this.updateCloseButtonVisibility();

    return normalized;
  },

  enableAnalytics() {
    if (!this.gaEnabled) {
      return;
    }

    this.setGaDisableFlag(false);

    if (!window.dataLayer) {
      window.dataLayer = [];
    }

    window.gtag = window.gtag || function gtag() {
      window.dataLayer.push(arguments);
    };

    if (!document.querySelector('script[data-cookie-ga]')) {
      const script = document.createElement('script');
      script.src = `https://www.googletagmanager.com/gtag/js?id=${this.gaId}`;
      script.async = true;
      script.dataset.cookieGa = '1';
      document.head.appendChild(script);
    }

    window.gtag('js', new Date());
    window.gtag('config', this.gaId, { anonymize_ip: true });

    this.state.analyticsLoaded = true;

    if (document.body) {
      document.body.classList.add('analytic-cookies-accepted');
    }
  },

  disableAnalytics() {
    if (!this.gaId) {
      return;
    }

    this.setGaDisableFlag(true);
    this.state.analyticsLoaded = false;

    if (document.body) {
      document.body.classList.remove('analytic-cookies-accepted');
    }
  },

  enableAds() {
    if (!this.adsEnabled) {
      return;
    }

    if (!this.state.adsConfigured) {
      window.adsbygoogle = window.adsbygoogle || [];
      window.adsbygoogle.push({
        google_ad_client: this.adsClientId,
        enable_page_level_ads: true,
      });
      this.state.adsConfigured = true;
    }

    if (!document.querySelector('script[data-cookie-google-ads]')) {
      const script = document.createElement('script');
      script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
      script.async = true;
      script.dataset.cookieGoogleAds = '1';
      document.head.appendChild(script);
    }

    this.state.adsLoaded = true;
  },

  disableAds() {
    if (!this.adsClientId) {
      return;
    }

    const script = document.querySelector('script[data-cookie-google-ads]');

    if (script) {
      script.remove();
    }

    if (window.adsbygoogle && Array.isArray(window.adsbygoogle)) {
      window.adsbygoogle.length = 0;
    }

    this.state.adsLoaded = false;
    this.state.adsConfigured = false;
  },

  setGaDisableFlag(value) {
    if (!this.gaId) {
      return;
    }

    window[`ga-disable-${this.gaId}`] = value;
  },

  showBanner() {
    if (!this.banner) {
      return;
    }

    this.lastFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    this.banner.classList.remove('hidden');
    this.banner.removeAttribute('aria-hidden');

    if (document.body) {
      document.body.classList.add('cookie-consent-open');
    }

    window.requestAnimationFrame(() => this.focusInitialElement());
  },

  hideBanner() {
    if (!this.banner) {
      return;
    }

    this.banner.classList.add('hidden');
    this.banner.setAttribute('aria-hidden', 'true');

    if (document.body) {
      document.body.classList.remove('cookie-consent-open');
    }

    if (this.lastFocusedElement && typeof this.lastFocusedElement.focus === 'function') {
      this.lastFocusedElement.focus();
    }
  },

  syncUIWithPreferences(preferences) {
    if (this.analyticsToggle) {
      this.analyticsToggle.checked = Boolean(preferences.analytics);
    }

    if (this.adsToggle) {
      this.adsToggle.checked = Boolean(preferences.ads);
    }
  },

  resetToggles() {
    if (this.analyticsToggle) {
      this.analyticsToggle.checked = false;
    }

    if (this.adsToggle) {
      this.adsToggle.checked = false;
    }
  },

  updateCloseButtonVisibility() {
    if (!this.closeButton) {
      return;
    }

    if (this.storedPreferences) {
      this.closeButton.classList.remove('hidden');
    } else {
      this.closeButton.classList.add('hidden');
    }
  },

  focusInitialElement() {
    if (!this.banner) {
      return;
    }

    const focusable = this.banner.querySelector('[data-cookie-accept], [data-cookie-save], [data-cookie-reject]');

    if (focusable && typeof focusable.focus === 'function') {
      focusable.focus();
    }
  },

  bindEvents() {
    if (this.acceptButton) {
      this.acceptButton.addEventListener('click', () => this.handleAcceptAll());
    }

    if (this.saveButton) {
      this.saveButton.addEventListener('click', () => this.handleSave());
    }

    if (this.rejectButton) {
      this.rejectButton.addEventListener('click', () => this.handleRejectAll());
    }

    if (this.closeButton) {
      this.closeButton.addEventListener('click', () => this.handleClose());
    }

    if (this.banner) {
      this.banner.addEventListener('click', (event) => {
        if (event.target === this.banner && this.storedPreferences) {
          this.handleClose();
        }
      });
    }

    document.addEventListener('keydown', (event) => {
      if (!this.isBannerVisible()) {
        return;
      }

      if (event.key === 'Tab') {
        this.handleFocusTrap(event);
        return;
      }

      if (event.key !== 'Escape') {
        return;
      }

      if (this.storedPreferences) {
        event.preventDefault();
        this.handleClose();
      }
    });
  },

  attachOpenButtons() {
    if (!this.openButtons.length || !this.banner) {
      return;
    }

    this.openButtons.forEach((button) => {
      button.addEventListener('click', (event) => {
        event.preventDefault();
        this.openPreferencesPanel();
      });
    });
  },

  openPreferencesPanel() {
    if (!this.banner) {
      return;
    }

    this.syncUIWithPreferences(this.currentPreferences);
    this.showBanner();
  },

  handleAcceptAll() {
    const preferences = this.normalizePreferences({
      analytics: true,
      ads: true,
    });

    this.applyPreferences(preferences, { persist: true, syncUI: true });
    this.hideBanner();
  },

  handleRejectAll() {
    const preferences = this.normalizePreferences({
      analytics: false,
      ads: false,
    });

    this.applyPreferences(preferences, { persist: true, syncUI: true });
    this.hideBanner();
  },

  handleSave() {
    const preferences = this.normalizePreferences({
      analytics: this.analyticsToggle ? this.analyticsToggle.checked : false,
      ads: this.adsToggle ? this.adsToggle.checked : false,
    });

    this.applyPreferences(preferences, { persist: true, syncUI: true });
    this.hideBanner();
  },

  handleClose() {
    if (!this.banner) {
      return;
    }

    if (this.storedPreferences) {
      this.currentPreferences = { ...this.storedPreferences };
      this.syncUIWithPreferences(this.currentPreferences);
    } else {
      this.resetToggles();
    }

    this.hideBanner();
  },

  isBannerVisible() {
    return Boolean(this.banner && !this.banner.classList.contains('hidden'));
  },

  handleFocusTrap(event) {
    if (!this.banner) {
      return;
    }

    const focusableElements = this.banner.querySelectorAll(
      'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    );

    if (!focusableElements.length) {
      return;
    }

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    const activeElement = document.activeElement;

    if (event.shiftKey && activeElement === firstElement) {
      event.preventDefault();
      lastElement.focus();
      return;
    }

    if (!event.shiftKey && activeElement === lastElement) {
      event.preventDefault();
      firstElement.focus();
    }
  },
};

export default cookies;
