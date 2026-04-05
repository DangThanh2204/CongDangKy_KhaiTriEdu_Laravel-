@php
    $browserSessionGuardSkipOnce = session()->pull('browser_session_guard_skip_once', false);
@endphp
<script>
    (function () {
        const config = {
            endpoint: @json(route('logout.browser-close')),
            loginUrl: @json(route('login')),
            csrfToken: @json(csrf_token()),
            skipCurrentLoad: @json($browserSessionGuardSkipOnce),
            storageKey: 'khai_tri_active_auth_tabs',
            tabIdKey: 'khai_tri_auth_tab_id',
            lastClosedKey: 'khai_tri_last_auth_browser_close',
            heartbeatMs: 15000,
            staleMs: 90000,
            reopenGapMs: 5000,
        };

        function readSessionValue(key) {
            try {
                return window.sessionStorage.getItem(key);
            } catch (error) {
                return null;
            }
        }

        function writeSessionValue(key, value) {
            try {
                window.sessionStorage.setItem(key, value);
            } catch (error) {}
        }

        function removeSessionValue(key) {
            try {
                window.sessionStorage.removeItem(key);
            } catch (error) {}
        }

        function readTabs() {
            try {
                const raw = window.localStorage.getItem(config.storageKey);
                if (!raw) {
                    return {};
                }

                const parsed = JSON.parse(raw);
                return parsed && typeof parsed === 'object' ? parsed : {};
            } catch (error) {
                return {};
            }
        }

        function writeTabs(tabs) {
            try {
                if (!tabs || Object.keys(tabs).length === 0) {
                    window.localStorage.removeItem(config.storageKey);
                    return;
                }

                window.localStorage.setItem(config.storageKey, JSON.stringify(tabs));
            } catch (error) {}
        }

        function readLastClosedAt() {
            try {
                return Number(window.localStorage.getItem(config.lastClosedKey) || 0);
            } catch (error) {
                return 0;
            }
        }

        function writeLastClosedAt(value) {
            try {
                if (value > 0) {
                    window.localStorage.setItem(config.lastClosedKey, String(value));
                } else {
                    window.localStorage.removeItem(config.lastClosedKey);
                }
            } catch (error) {}
        }

        function pruneTabs(tabs, now) {
            const freshTabs = {};
            let removedStaleTabs = false;

            Object.entries(tabs).forEach(([id, timestamp]) => {
                const numericTimestamp = Number(timestamp);

                if (Number.isFinite(numericTimestamp) && now - numericTimestamp <= config.staleMs) {
                    freshTabs[id] = numericTimestamp;
                } else {
                    removedStaleTabs = true;
                }
            });

            return {
                tabs: freshTabs,
                removedStaleTabs,
            };
        }

        function createTabId() {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                return window.crypto.randomUUID();
            }

            return 'tab-' + Date.now() + '-' + Math.random().toString(36).slice(2);
        }

        function clearClientState() {
            writeTabs({});
            writeLastClosedAt(0);
            removeSessionValue(config.tabIdKey);
        }

        function redirectToLogin() {
            window.location.replace(config.loginUrl);
        }

        function sendBackgroundLogout() {
            const body = new URLSearchParams({
                _token: config.csrfToken,
            });

            if (navigator.sendBeacon) {
                try {
                    const payload = new Blob([body.toString()], {
                        type: 'application/x-www-form-urlencoded;charset=UTF-8',
                    });

                    navigator.sendBeacon(config.endpoint, payload);
                    return Promise.resolve();
                } catch (error) {}
            }

            return fetch(config.endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: body.toString(),
                credentials: 'same-origin',
                keepalive: true,
            }).catch(() => null);
        }

        let tabId = readSessionValue(config.tabIdKey);
        let hadSessionTabId = Boolean(tabId);

        if (config.skipCurrentLoad) {
            clearClientState();
            tabId = null;
            hadSessionTabId = false;
        }

        if (!tabId) {
            tabId = createTabId();
            writeSessionValue(config.tabIdKey, tabId);
        }

        const now = Date.now();
        const prunedState = pruneTabs(readTabs(), now);
        let tabs = prunedState.tabs;
        const hasFreshTabs = Object.keys(tabs).length > 0;
        const currentSeenAt = Object.prototype.hasOwnProperty.call(tabs, tabId)
            ? Number(tabs[tabId])
            : null;
        const lastClosedAt = readLastClosedAt();
        const closeGap = lastClosedAt > 0 ? now - lastClosedAt : 0;

        const shouldLogoutForClosedBrowser = (
            (!hasFreshTabs && prunedState.removedStaleTabs) ||
            (
                !hasFreshTabs &&
                lastClosedAt > 0 &&
                closeGap > config.reopenGapMs &&
                (!hadSessionTabId || currentSeenAt === null)
            ) ||
            (currentSeenAt !== null && now - currentSeenAt > config.staleMs)
        );

        if (shouldLogoutForClosedBrowser) {
            clearClientState();
            sendBackgroundLogout().finally(redirectToLogin);
            return;
        }

        function recordHeartbeat() {
            const state = pruneTabs(readTabs(), Date.now());
            tabs = state.tabs;
            tabs[tabId] = Date.now();
            writeTabs(tabs);
            writeLastClosedAt(0);
        }

        function removeCurrentTab() {
            const state = pruneTabs(readTabs(), Date.now());
            tabs = state.tabs;

            if (Object.prototype.hasOwnProperty.call(tabs, tabId)) {
                delete tabs[tabId];
            }

            if (Object.keys(tabs).length === 0) {
                writeTabs({});
                writeLastClosedAt(Date.now());
            } else {
                writeTabs(tabs);
            }
        }

        recordHeartbeat();
        const heartbeatTimer = window.setInterval(recordHeartbeat, config.heartbeatMs);

        window.addEventListener('pagehide', removeCurrentTab);
        window.addEventListener('beforeunload', removeCurrentTab);
        window.addEventListener('pageshow', recordHeartbeat);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                recordHeartbeat();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[data-browser-session-logout]').forEach(function (form) {
                form.addEventListener('submit', function () {
                    window.clearInterval(heartbeatTimer);
                    clearClientState();
                });
            });
        });
    })();
</script>
