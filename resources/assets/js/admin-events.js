document.addEventListener('DOMContentLoaded', () => {
  const results = document.querySelector('#events-results');

  if (!results) {
    return;
  }

  const filterForm = document.querySelector('[data-events-filter]');
  const pendingAlert = document.querySelector('#events-pending-alert');
  const feedback = document.querySelector('#events-feedback');
  let activeRequest;

  const showFeedback = (message, type = 'success') => {
    feedback.textContent = message;
    feedback.className = `alert alert-${type}`;
    feedback.hidden = false;
  };

  const syncFilters = url => {
    const params = new URL(url).searchParams;

    filterForm.elements.search.value = params.get('search') || '';
    filterForm.elements.status.value = params.get('status') || '';
  };

  const loadResults = async (url, { updateHistory = false, scroll = false } = {}) => {
    activeRequest?.abort();
    const controller = new AbortController();
    activeRequest = controller;
    results.classList.add('is-loading');
    results.setAttribute('aria-busy', 'true');

    try {
      const response = await fetch(url, {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        signal: controller.signal
      });

      if (!response.ok) {
        throw new Error('Impossible de charger les événements. Réessayez.');
      }

      const data = await response.json();
      if (typeof data.html !== 'string' || typeof data.lastPage !== 'number') {
        throw new Error('La réponse reçue est invalide. Rechargez la page et réessayez.');
      }
      const requestedUrl = new URL(url, window.location.href);

      if (data.currentPage > data.lastPage) {
        requestedUrl.searchParams.set('page', data.lastPage);
        window.history.replaceState({}, '', requestedUrl);
        return loadResults(requestedUrl.href, { updateHistory, scroll });
      }

      results.innerHTML = data.html;
      pendingAlert.hidden = data.pendingCount < 1;
      pendingAlert.classList.toggle('d-none', data.pendingCount < 1);
      pendingAlert.textContent = `${data.pendingCount} événement(s) en attente de validation.`;
      syncFilters(requestedUrl);

      if (updateHistory) {
        window.history.pushState({}, '', requestedUrl);
      }
      if (scroll) {
        results.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      return true;
    } catch (error) {
      if (error.name !== 'AbortError') {
        showFeedback(error.message, 'danger');
      }

      return false;
    } finally {
      if (activeRequest === controller) {
        results.classList.remove('is-loading');
        results.removeAttribute('aria-busy');
        activeRequest = null;
      }
    }
  };

  filterForm.addEventListener('submit', event => {
    event.preventDefault();
    const url = new URL(filterForm.action, window.location.href);
    new FormData(filterForm).forEach((value, key) => {
      if (value) {
        url.searchParams.set(key, value);
      }
    });
    url.searchParams.delete('page');
    loadResults(url, { updateHistory: true });
  });

  filterForm.elements.status.addEventListener('change', () => {
    filterForm.requestSubmit();
  });

  document.addEventListener('click', event => {
    const link = event.target.closest('#events-results .pagination a');

    if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      return;
    }

    event.preventDefault();
    loadResults(link.href, { updateHistory: true, scroll: true });
  });

  document.addEventListener('submit', async event => {
    const form = event.target.closest('[data-event-action]');

    if (!form) {
      return;
    }

    event.preventDefault();
    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
      return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      const data = response.headers.get('content-type')?.includes('application/json')
        ? await response.json()
        : {};

      if (!response.ok) {
        const validationErrors = Object.values(data.errors || {}).flat();
        throw new Error(validationErrors[0] || data.message || 'L’action n’a pas pu être effectuée.');
      }

      showFeedback(data.message);
      if (!await loadResults(window.location.href)) {
        submitButton.disabled = false;
      }
    } catch (error) {
      showFeedback(error.message, 'danger');
      submitButton.disabled = false;
    }
  });

  window.addEventListener('popstate', () => {
    syncFilters(window.location.href);
    loadResults(window.location.href);
  });
});
