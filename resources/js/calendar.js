/**
 * Riviera Events — Calendar JS
 * Vanilla JS + Fetch API, no framework dependencies.
 * Works inside iframe embeds (respects window.calendarConfig.isEmbed).
 */

class EventCalendar {
    constructor(config) {
        this.apiUrl = config.apiUrl;
        this.filterOptionsUrl = config.filterOptionsUrl;
        this.isEmbed = config.isEmbed || false;

        this.state = {
            search: '',
            location: '',
            category: '',
            start: '',
            end: '',
            premium: false,
            page: 1,
            view: 'list',
        };

        this.debounceTimer = null;
        this.currentPage = 1;
        this.lastMeta = null;

        // Month/Week/Day navigation state
        this._currentMonthDate = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
        this._currentWeekStart = this._startOfWeek(new Date());
        this._currentDay = new Date();

        this.els = {
            list: document.getElementById('events-list'),
            month: document.getElementById('events-month'),
            week: document.getElementById('events-week'),
            day: document.getElementById('events-day'),
            loading: document.getElementById('events-loading'),
            empty: document.getElementById('events-empty'),
            pagination: document.getElementById('events-pagination'),
            search: document.getElementById('filter-search'),
            location: document.getElementById('filter-location'),
            category: document.getElementById('filter-category'),
            start: document.getElementById('filter-start'),
            end: document.getElementById('filter-end'),
            premium: document.getElementById('filter-premium'),
        };

        if (!this.els.list) return; // Not on calendar page

        this._readUrlState();
        this._loadFilterOptions();
        this._bindEvents();
        this._fetchEvents();
    }

    // ─── State management ──────────────────────────────────────────────────────

    _readUrlState() {
        const params = new URLSearchParams(window.location.search);
        this.state.search   = params.get('search')   || '';
        this.state.location = params.get('location') || '';
        this.state.category = params.get('category') || '';
        this.state.start    = params.get('start')    || '';
        this.state.end      = params.get('end')      || '';
        this.state.premium  = params.get('premium')  === '1';
        this.state.view     = params.get('view')     || 'list';
        this.currentPage    = parseInt(params.get('page') || '1', 10);
    }

    _syncUrlState() {
        if (this.isEmbed) return; // Don't mutate URL in iframe

        const params = new URLSearchParams();
        if (this.state.search)   params.set('search', this.state.search);
        if (this.state.location) params.set('location', this.state.location);
        if (this.state.category) params.set('category', this.state.category);
        if (this.state.start)    params.set('start', this.state.start);
        if (this.state.end)      params.set('end', this.state.end);
        if (this.state.premium)  params.set('premium', '1');
        if (this.state.view !== 'list') params.set('view', this.state.view);
        if (this.currentPage > 1) params.set('page', this.currentPage);

        const qs = params.toString();
        history.replaceState({}, '', qs ? `?${qs}` : window.location.pathname);
    }

    // ─── Filter options ────────────────────────────────────────────────────────

    async _loadFilterOptions() {
        try {
            const res = await fetch(this.filterOptionsUrl);
            const data = await res.json();

            this._populateSelect(this.els.location, data.locations, 'id', loc => `${loc.city}, ${loc.state}`);
            this._populateSelect(this.els.category, data.categories, 'id', cat => cat.name);

            // Restore selections from URL state
            if (this.state.location && this.els.location) this.els.location.value = this.state.location;
            if (this.state.category && this.els.category) this.els.category.value = this.state.category;
        } catch (e) {
            console.error('Failed to load filter options', e);
        }
    }

    _populateSelect(select, items, valueKey, labelFn) {
        if (!select || !items) return;
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item[valueKey];
            opt.textContent = labelFn(item);
            select.appendChild(opt);
        });
    }

    // ─── Event binding ─────────────────────────────────────────────────────────

    _bindEvents() {
        const { search, location, category, start, end, premium } = this.els;

        if (search) {
            search.value = this.state.search;
            search.addEventListener('input', e => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.state.search = e.target.value.trim();
                    this._reset();
                }, 400);
            });
        }

        const immediate = (key, el) => {
            if (!el) return;
            el.addEventListener('change', e => {
                this.state[key] = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
                this._reset();
            });
        };

        immediate('location', location);
        immediate('category', category);
        immediate('start', start);
        immediate('end', end);
        immediate('premium', premium);

        // Restore UI state
        if (start) start.value = this.state.start;
        if (end) end.value = this.state.end;
        if (premium) premium.checked = this.state.premium;

        // View toggle buttons
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this._setView(btn.dataset.view);
            });
        });

        this._setView(this.state.view, false);
    }

    _setView(view, fetch = true) {
        this.state.view = view;

        document.querySelectorAll('.view-btn').forEach(btn => {
            const active = btn.dataset.view === view;
            btn.classList.toggle('view-btn-active', active);
            btn.classList.toggle('view-btn-inactive', !active);
        });

        if (this.els.list)  this.els.list.classList.toggle('hidden', view !== 'list');
        if (this.els.month) this.els.month.classList.toggle('hidden', view !== 'month');
        if (this.els.week)  this.els.week.classList.toggle('hidden', view !== 'week');
        if (this.els.day)   this.els.day.classList.toggle('hidden', view !== 'day');

        // For week/day views, auto-set date range and use large page size
        if (view === 'month') {
            this._currentMonthDate = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
            this._applyMonthDateRange();
        } else if (view === 'week') {
            this._currentWeekStart = this._startOfWeek(new Date());
            this._applyWeekDateRange();
        } else if (view === 'day') {
            this._currentDay = new Date();
            this._applyDayDateRange();
        }

        if (fetch) this._reset();
    }

    _applyMonthDateRange() {
        const y = this._currentMonthDate.getFullYear();
        const m = this._currentMonthDate.getMonth();
        this.state.start = this._formatDate(new Date(y, m, 1));
        this.state.end   = this._formatDate(new Date(y, m + 1, 0));
    }

    _applyWeekDateRange() {
        const end = this._addDays(this._currentWeekStart, 6);
        this.state.start = this._formatDate(this._currentWeekStart);
        this.state.end   = this._formatDate(end);
    }

    _applyDayDateRange() {
        const d = this._formatDate(this._currentDay);
        this.state.start = d;
        this.state.end   = d;
    }

    _reset() {
        this.currentPage = 1;
        this._fetchEvents();
    }

    // ─── Fetch & render ────────────────────────────────────────────────────────

    _buildUrl() {
        const params = new URLSearchParams();
        if (this.state.search)   params.set('search', this.state.search);
        if (this.state.location) params.set('location', this.state.location);
        if (this.state.category) params.set('category', this.state.category);
        if (this.state.start)    params.set('start', this.state.start);
        if (this.state.end)      params.set('end', this.state.end);
        if (this.state.premium)  params.set('premium', '1');

        // Non-list views fetch all events without pagination
        if (this.state.view === 'week' || this.state.view === 'day' || this.state.view === 'month') {
            params.set('per_page', '400');
        } else {
            if (this.currentPage > 1) params.set('page', this.currentPage);
        }

        return `${this.apiUrl}?${params.toString()}`;
    }

    async _fetchEvents() {
        this._showLoading(true);

        try {
            const res = await fetch(this._buildUrl(), {
                headers: { 'Accept': 'application/json' },
            });

            if (!res.ok) throw new Error('API error');

            const json = await res.json();
            this.lastMeta = json.meta;

            this._render(json.data, json.meta);

            // Only show pagination for list view
            if (this.state.view === 'list') {
                this._renderPagination(json.meta);
            } else if (this.els.pagination) {
                this.els.pagination.classList.add('hidden');
            }

            this._syncUrlState();
        } catch (e) {
            console.error('Failed to fetch events', e);
        } finally {
            this._showLoading(false);
        }
    }

    _render(events, meta) {
        if (this.state.view === 'month') {
            this._renderMonth(events);
        } else if (this.state.view === 'week') {
            this._renderWeek(events);
        } else if (this.state.view === 'day') {
            this._renderDay(events);
        } else {
            this._renderList(events);
        }

        if (this.els.empty) {
            this.els.empty.classList.toggle('hidden', events.length > 0);
        }
    }

    _renderList(events) {
        if (!this.els.list) return;

        this.els.list.innerHTML = '';

        events.forEach((event, index) => {
            // Inject native ad slot every 5 events (handled server-side via AdService in Phase 6)
            if (event._type === 'ad') {
                this.els.list.insertAdjacentHTML('beforeend', this._adCardHtml(event.ad));
                return;
            }

            this.els.list.insertAdjacentHTML('beforeend', this._eventCardHtml(event));
        });
    }

    _renderMonth(events) {
        if (!this.els.month) return;

        const year = this._currentMonthDate.getFullYear();
        const month = this._currentMonthDate.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();

        const monthNames = ['January','February','March','April','May','June',
                            'July','August','September','October','November','December'];

        // Index events by date
        const byDate = {};
        events.forEach(ev => {
            if (!ev.start_date) return;
            const d = ev.start_date.split('T')[0];
            if (!byDate[d]) byDate[d] = [];
            byDate[d].push(ev);
        });

        let html = `<div class="bg-white overflow-hidden" style="border: 1px solid var(--color-border); border-radius: 2px;">`;
        html += `<div class="flex items-center justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border)">
            <button id="month-prev" class="px-3 py-1.5 text-sm" style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-muted)">← Prev</button>
            <h2 style="font-family: 'DM Serif Display', Georgia, serif; color: var(--color-ink)">${monthNames[month]} ${year}</h2>
            <button id="month-next" class="px-3 py-1.5 text-sm" style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-muted)">Next →</button>
        </div>`;

        html += `<div class="grid grid-cols-7 text-xs text-center border-b" style="color: var(--color-muted); border-color: var(--color-border)">`;
        ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => {
            html += `<div class="py-2">${d}</div>`;
        });
        html += `</div>`;

        html += `<div class="grid grid-cols-7">`;

        // Empty cells for first week
        for (let i = 0; i < firstDay; i++) {
            html += `<div class="aspect-square p-1 border-r border-b" style="border-color: var(--color-border); background-color: var(--color-paper)"></div>`;
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
            const dayEvents = byDate[dateStr] || [];
            const isToday = day === today.getDate() && month === today.getMonth() && year === today.getFullYear();

            html += `<div class="aspect-square p-1 border-r border-b overflow-hidden cursor-pointer" style="border-color: var(--color-border); ${isToday ? 'background-color: #fef2ee;' : ''}">`;
            html += `<div class="text-xs font-medium mb-0.5" style="color: ${isToday ? 'var(--color-accent)' : 'var(--color-ink)'}">${day}</div>`;
            dayEvents.slice(0, 10).forEach(ev => {
                const color = ev.category?.color || '#6B7280';
                html += `<a href="/events/${ev.slug}" class="block truncate text-xs px-0.5 text-white mb-0.5" style="background-color:${color}; border-radius: 1px" title="${ev.title}">${ev.title}</a>`;
            });
            if (dayEvents.length > 10) {
                html += `<button class="month-more-btn text-xs font-medium mt-0.5" style="color: var(--color-accent)" data-date="${dateStr}">+${dayEvents.length - 10} more</button>`;
            }
            html += `</div>`;
        }

        html += `</div></div>`;

        this.els.month.innerHTML = html;

        // Bind prev/next navigation
        document.getElementById('month-prev')?.addEventListener('click', () => {
            this._currentMonthDate = new Date(year, month - 1, 1);
            this._applyMonthDateRange();
            this._fetchEvents();
        });
        document.getElementById('month-next')?.addEventListener('click', () => {
            this._currentMonthDate = new Date(year, month + 1, 1);
            this._applyMonthDateRange();
            this._fetchEvents();
        });

        // Bind "+X more" buttons to switch to day view for that date
        this.els.month.querySelectorAll('.month-more-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const [y, m, d] = btn.dataset.date.split('-').map(Number);
                this._currentDay = new Date(y, m - 1, d);
                this._applyDayDateRange();
                this._setView('day');
            });
        });
    }

    _renderWeek(events) {
        if (!this.els.week) return;

        const dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        const today = this._formatDate(new Date());

        // Build 7-day columns starting from _currentWeekStart (Monday)
        const days = [];
        for (let i = 0; i < 7; i++) {
            const d = this._addDays(this._currentWeekStart, i);
            days.push({ date: d, dateStr: this._formatDate(d) });
        }

        // Index events by date
        const byDate = {};
        events.forEach(ev => {
            if (!ev.start_date) return;
            const d = ev.start_date.split('T')[0];
            if (!byDate[d]) byDate[d] = [];
            byDate[d].push(ev);
        });

        const weekLabel = `${this._formatDateLong(days[0].date)} – ${this._formatDateLong(days[6].date)}`;

        let html = `<div class="bg-white overflow-hidden" style="border: 1px solid var(--color-border); border-radius: 2px;">`;

        // Navigation header
        html += `<div class="flex items-center justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border)">
            <button id="week-prev" class="px-3 py-1.5 text-sm" style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-muted)">← Prev</button>
            <h2 class="text-sm" style="font-family: 'DM Serif Display', Georgia, serif; color: var(--color-ink)">${weekLabel}</h2>
            <button id="week-next" class="px-3 py-1.5 text-sm" style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-muted)">Next →</button>
        </div>`;

        // Day header row
        html += `<div class="grid grid-cols-7" style="border-bottom: 1px solid var(--color-border)">`;
        days.forEach((d, i) => {
            const isToday = d.dateStr === today;
            html += `<div class="py-2 px-1 text-center border-r last:border-r-0" style="border-color: var(--color-border)">
                <div class="text-xs" style="color: var(--color-muted)">${dayNames[i]}</div>
                <div class="text-sm font-semibold" style="color: ${isToday ? 'var(--color-accent)' : 'var(--color-ink)'}">${d.date.getDate()}</div>
            </div>`;
        });
        html += `</div>`;

        // Events row
        html += `<div class="grid grid-cols-7 min-h-32">`;
        days.forEach(d => {
            const dayEvents = byDate[d.dateStr] || [];
            const isToday = d.dateStr === today;
            html += `<div class="p-1 border-r last:border-r-0" style="border-color: var(--color-border); ${isToday ? 'background-color: #fef2ee;' : ''}">`;
            if (dayEvents.length === 0) {
                html += `<div class="text-xs text-center mt-2" style="color: var(--color-border)">—</div>`;
            } else {
                dayEvents.forEach(ev => {
                    const color = ev.category?.color || '#6B7280';
                    const time = ev.is_all_day ? 'All Day' : this._formatTime(ev.start_date);
                    html += `<a href="/events/${ev.slug}" class="block mb-1 p-1 text-white text-xs hover:opacity-90 transition-opacity" style="background-color:${color}; border-radius: 1px" title="${this._esc(ev.title)}">
                        <div class="font-medium truncate">${this._esc(ev.title)}</div>
                        <div class="opacity-80">${time}</div>
                    </a>`;
                });
            }
            html += `</div>`;
        });
        html += `</div></div>`;

        this.els.week.innerHTML = html;

        // Bind prev/next navigation
        document.getElementById('week-prev')?.addEventListener('click', () => {
            this._currentWeekStart = this._addDays(this._currentWeekStart, -7);
            this._applyWeekDateRange();
            this._fetchEvents();
        });
        document.getElementById('week-next')?.addEventListener('click', () => {
            this._currentWeekStart = this._addDays(this._currentWeekStart, 7);
            this._applyWeekDateRange();
            this._fetchEvents();
        });
    }

    _renderDay(events) {
        if (!this.els.day) return;

        const dateLabel = this._currentDay.toLocaleDateString('en-US', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

        // Sort: all-day first, then by start time
        const allDay = events.filter(ev => ev.is_all_day);
        const timed  = events.filter(ev => !ev.is_all_day).sort((a, b) =>
            (a.start_date || '').localeCompare(b.start_date || '')
        );

        let html = `<div class="bg-white overflow-hidden" style="border: 1px solid var(--color-border); border-radius: 2px;">`;

        // Navigation header
        html += `<div class="flex items-center justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border)">
            <button id="day-prev" class="px-3 py-1.5 text-sm" style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-muted)">← Prev</button>
            <h2 style="font-family: 'DM Serif Display', Georgia, serif; color: var(--color-ink)">${dateLabel}</h2>
            <button id="day-next" class="px-3 py-1.5 text-sm" style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-muted)">Next →</button>
        </div>`;

        if (events.length === 0) {
            html += `<div class="py-16 text-center" style="color: var(--color-muted)">
                <p class="text-lg font-display" style="font-family: 'DM Serif Display', Georgia, serif">No events on this day</p>
            </div>`;
        } else {
            html += `<div class="divide-y" style="border-color: var(--color-border)">`;

            // All-day events
            allDay.forEach(ev => {
                const cat = ev.category;
                const color = cat?.color || '#6B7280';
                html += `<a href="/events/${ev.slug}" class="flex items-center gap-3 px-4 py-3 transition-colors" style="color: inherit" onmouseover="this.style.backgroundColor='var(--color-paper)'" onmouseout="this.style.backgroundColor=''">
                    <div class="w-16 text-xs flex-shrink-0 font-medium" style="color: var(--color-muted)">All Day</div>
                    <div class="w-1 h-8 flex-shrink-0" style="background-color:${color}; border-radius: 1px"></div>
                    <div class="min-w-0">
                        <div class="font-medium truncate" style="color: var(--color-ink)">${this._esc(ev.title)}</div>
                        ${ev.location ? `<div class="text-xs" style="color: var(--color-muted)">${this._esc(ev.location.city)}</div>` : ''}
                    </div>
                    ${ev.is_premium ? `<span class="ml-auto flex-shrink-0 text-xs font-bold uppercase tracking-wider px-2 py-0.5" style="border-radius: 1px; background-color: #fef9c3; color: #92400e">★ Featured</span>` : ''}
                </a>`;
            });

            // Timed events
            timed.forEach(ev => {
                const cat = ev.category;
                const color = cat?.color || '#6B7280';
                const time = this._formatTime(ev.start_date);
                html += `<a href="/events/${ev.slug}" class="flex items-center gap-3 px-4 py-3 transition-colors" style="color: inherit" onmouseover="this.style.backgroundColor='var(--color-paper)'" onmouseout="this.style.backgroundColor=''">
                    <div class="w-16 text-xs flex-shrink-0 font-medium" style="color: var(--color-muted)">${time}</div>
                    <div class="w-1 h-8 flex-shrink-0" style="background-color:${color}; border-radius: 1px"></div>
                    <div class="min-w-0">
                        <div class="font-medium truncate" style="color: var(--color-ink)">${this._esc(ev.title)}</div>
                        ${ev.location ? `<div class="text-xs" style="color: var(--color-muted)">${this._esc(ev.location.city)}</div>` : ''}
                    </div>
                    ${ev.is_premium ? `<span class="ml-auto flex-shrink-0 text-xs font-bold uppercase tracking-wider px-2 py-0.5" style="border-radius: 1px; background-color: #fef9c3; color: #92400e">★ Featured</span>` : ''}
                </a>`;
            });

            html += `</div>`;
        }

        html += `</div>`;
        this.els.day.innerHTML = html;

        // Bind prev/next navigation
        document.getElementById('day-prev')?.addEventListener('click', () => {
            this._currentDay = this._addDays(this._currentDay, -1);
            this._applyDayDateRange();
            this._fetchEvents();
        });
        document.getElementById('day-next')?.addEventListener('click', () => {
            this._currentDay = this._addDays(this._currentDay, 1);
            this._applyDayDateRange();
            this._fetchEvents();
        });
    }

    _eventCardHtml(event) {
        // Parse date from the YYYY-MM-DD portion only to avoid UTC→local timezone shifts
        const parts = event.start_date ? event.start_date.split('T')[0].split('-') : null;
        const d = parts ? new Date(+parts[0], +parts[1] - 1, +parts[2]) : null;
        const month = d ? d.toLocaleDateString('en-US', { month: 'short' }).toUpperCase() : '';
        const day = d ? d.getDate() : '';
        const catColor = event.category?.color || 'var(--color-accent)';
        const catName = event.category ? this._esc(event.category.name) : '';

        const dateFlag = d ? `
            <div class="flex items-stretch" style="border-bottom: 1px solid var(--color-border)">
                <div class="text-white px-3 py-2 text-center flex-shrink-0" style="min-width: 52px; background-color: var(--color-accent)">
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em">${month}</div>
                    <div style="font-size: 1.25rem; font-weight: 700; line-height: 1">${day}</div>
                </div>
                <div class="px-3 py-2 flex items-center flex-1 gap-2">
                    ${catName ? `<span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: ${catColor}">${catName}</span>` : ''}
                    ${event.is_premium ? `<span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-accent-2)">★ Featured</span>` : ''}
                </div>
            </div>` : '';

        const img = event.image_url
            ? `<div class="aspect-video overflow-hidden"><img src="${event.image_url}" alt="${this._esc(event.title)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy"></div>`
            : '';

        const loc = event.location
            ? `<p class="text-sm mt-1" style="color: var(--color-muted)">${this._esc(event.location.city)}</p>`
            : '';

        return `
        <a href="/events/${event.slug}" class="block bg-white overflow-hidden group transition-all duration-200 hover:shadow-lg" style="border: 1px solid var(--color-border); border-radius: 2px;">
            ${dateFlag}
            ${img}
            <div class="p-4">
                <h3 class="font-display text-lg leading-snug line-clamp-2" style="color: var(--color-ink); font-family: 'DM Serif Display', Georgia, serif">${this._esc(event.title)}</h3>
                ${loc}
                ${event.excerpt ? `<p class="text-sm mt-2 line-clamp-2" style="color: var(--color-muted)">${this._esc(event.excerpt)}</p>` : ''}
            </div>
        </a>`;
    }

    _adCardHtml(ad) {
        return `
        <a href="${this._esc(ad.url)}" target="_blank" rel="noopener sponsored" aria-label="Sponsored advertisement"
           class="block bg-yellow-50 border border-yellow-200 rounded-xl p-4 hover:bg-yellow-100 transition-colors">
            <div class="flex items-center gap-3">
                ${ad.image_url ? `<img src="${this._esc(ad.image_url)}" alt="${this._esc(ad.title)}" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">` : ''}
                <div>
                    <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide mb-0.5">Sponsored</p>
                    <p class="font-semibold text-gray-900 text-sm">${this._esc(ad.title)}</p>
                </div>
            </div>
        </a>`;
    }

    // ─── Pagination ────────────────────────────────────────────────────────────

    _renderPagination(meta) {
        if (!this.els.pagination || !meta) return;

        const { current_page, last_page } = meta;

        if (last_page <= 1) {
            this.els.pagination.classList.add('hidden');
            return;
        }

        this.els.pagination.classList.remove('hidden');
        this.els.pagination.innerHTML = '';

        const btn = (label, page, disabled = false) => {
            const el = document.createElement('button');
            el.textContent = label;
            el.style.cssText = `border: 1px solid var(--color-border); border-radius: 2px; padding: 6px 12px; font-size: 0.875rem; cursor: ${disabled ? 'not-allowed' : 'pointer'}; opacity: ${disabled ? '0.4' : '1'}; background: var(--color-surface); color: var(--color-muted);`;
            if (!disabled) {
                el.addEventListener('mouseover', () => { el.style.backgroundColor = 'var(--color-paper)'; });
                el.addEventListener('mouseout', () => { el.style.backgroundColor = 'var(--color-surface)'; });
                el.addEventListener('click', () => {
                    this.currentPage = page;
                    this._fetchEvents();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }
            return el;
        };

        this.els.pagination.appendChild(btn('← Prev', current_page - 1, current_page === 1));

        for (let p = Math.max(1, current_page - 2); p <= Math.min(last_page, current_page + 2); p++) {
            const el = btn(p, p);
            if (p === current_page) {
                el.style.cssText = `border: 1px solid var(--color-accent); border-radius: 2px; padding: 6px 12px; font-size: 0.875rem; cursor: pointer; background-color: var(--color-accent); color: white;`;
            }
            this.els.pagination.appendChild(el);
        }

        this.els.pagination.appendChild(btn('Next →', current_page + 1, current_page === last_page));
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    _showLoading(show) {
        if (this.els.loading) this.els.loading.classList.toggle('hidden', !show);
        if (this.els.list)  this.els.list.classList.toggle('hidden', show || this.state.view !== 'list');
        if (this.els.month) this.els.month.classList.toggle('hidden', show || this.state.view !== 'month');
        if (this.els.week)  this.els.week.classList.toggle('hidden', show || this.state.view !== 'week');
        if (this.els.day)   this.els.day.classList.toggle('hidden', show || this.state.view !== 'day');
    }

    /** Returns Monday of the week containing `date` */
    _startOfWeek(date) {
        const d = new Date(date);
        const day = d.getDay(); // 0=Sun, 1=Mon ... 6=Sat
        const diff = (day === 0) ? -6 : 1 - day; // shift to Monday
        d.setDate(d.getDate() + diff);
        d.setHours(0, 0, 0, 0);
        return d;
    }

    /** Returns a new Date `n` days from `date` */
    _addDays(date, n) {
        const d = new Date(date);
        d.setDate(d.getDate() + n);
        return d;
    }

    /** Returns YYYY-MM-DD string for `date` */
    _formatDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    /** Returns "Mar 4" style string for `date` */
    _formatDateLong(date) {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    /** Returns "9:00 AM" style string from ISO datetime string */
    _formatTime(isoString) {
        if (!isoString) return '';
        const d = new Date(isoString);
        return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    _esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
}

// Boot on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    if (window.calendarConfig) {
        window.calendar = new EventCalendar(window.calendarConfig);
    }
});
