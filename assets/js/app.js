(function () {
  'use strict';
  var isStaticHosting = /\.github\.io$/i.test(window.location.hostname);

  var root = document.documentElement;
  var stored = localStorage.getItem('theme');
  if (stored === 'dark' || stored === 'light') {
    root.setAttribute('data-theme', stored);
  } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    root.setAttribute('data-theme', 'dark');
  }

  document.querySelectorAll('.theme-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      localStorage.setItem('theme', next);
    });
  });

  var navToggle = document.querySelector('.nav-toggle');
  var mainNav = document.querySelector('.main-nav');
  if (navToggle && mainNav) {
    navToggle.addEventListener('click', function () {
      var open = mainNav.classList.toggle('is-open');
      navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  if (typeof AOS !== 'undefined') {
    AOS.init({ duration: 700, once: true, offset: 40 });
  }

  var base = (function () {
    var m = document.querySelector('link[href*="assets/css/style.css"]');
    if (!m || !m.href) return '';
    var u = m.href.replace(/\/assets\/css\/style\.css.*$/, '');
    return u.replace(/\/$/, '');
  })();

  function apiUrl(path) {
    path = path.replace(/^\//, '');
    return base ? base + '/' + path : '/' + path;
  }

  var nf = document.getElementById('newsletter-form');
  if (nf) {
    nf.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(nf);
      var msg = document.getElementById('newsletter-msg');
      if (isStaticHosting || /\.php($|\?)/i.test(nf.action || '')) {
        msg.textContent = 'Mode statis: form aktif saat dihosting PHP.';
        return;
      }
      msg.textContent = 'Mengirim…';
      fetch(nf.action, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          msg.textContent = data.message || (data.ok ? 'Berhasil.' : 'Gagal.');
          if (data.ok) nf.reset();
        })
        .catch(function () {
          msg.textContent = 'Terjadi kesalahan jaringan.';
        });
    });
  }

  var cf = document.getElementById('contact-form');
  if (cf) {
    cf.addEventListener('submit', function (e) {
      e.preventDefault();
      var msg = document.getElementById('contact-msg');
      if (isStaticHosting || /\.php($|\?)/i.test(cf.action || '')) {
        msg.textContent = 'Mode statis: form aktif saat dihosting PHP.';
        return;
      }
      msg.textContent = 'Mengirim…';
      var fd = new FormData(cf);
      fetch(cf.action, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          msg.textContent = data.message || '';
          if (data.ok) cf.reset();
        })
        .catch(function () {
          msg.textContent = 'Gagal mengirim.';
        });
    });
  }

  var searchInput = document.getElementById('articles-search');
  var liveBox = document.getElementById('live-search-results');
  var searchTimer;
  if (searchInput && liveBox) {
    searchInput.addEventListener('input', function () {
      clearTimeout(searchTimer);
      var q = searchInput.value.trim();
      if (q.length < 2) {
        liveBox.classList.remove('is-visible');
        liveBox.innerHTML = '';
        return;
      }
      searchTimer = setTimeout(function () {
        if (isStaticHosting) {
          liveBox.classList.remove('is-visible');
          return;
        }
        fetch(apiUrl('api/search.php?q=' + encodeURIComponent(q)), { credentials: 'same-origin' })
          .then(function (r) {
            return r.json();
          })
          .then(function (data) {
            liveBox.innerHTML = '';
            (data.results || []).forEach(function (item) {
              var a = document.createElement('a');
              a.href = item.url;
              a.textContent = item.title;
              a.setAttribute('role', 'option');
              liveBox.appendChild(a);
            });
            liveBox.classList.toggle('is-visible', (data.results || []).length > 0);
          });
      }, 280);
    });
    document.addEventListener('click', function (ev) {
      if (!liveBox.contains(ev.target) && ev.target !== searchInput) {
        liveBox.classList.remove('is-visible');
      }
    });
  }

  var loadMore = document.getElementById('load-more-articles');
  if (loadMore) {
    loadMore.addEventListener('click', function () {
      if (isStaticHosting) return;
      var page = parseInt(loadMore.getAttribute('data-page'), 10) || 1;
      var next = page + 1;
      var qs = loadMore.getAttribute('data-query') || '';
      var url = apiUrl('api/load-articles.php?' + qs + (qs ? '&' : '') + 'page=' + next);
      loadMore.disabled = true;
      loadMore.textContent = 'Memuat…';
      var grid = document.getElementById('articles-grid');
      var skels = [];
      if (grid) {
        for (var s = 0; s < 2; s++) {
          var sk = document.createElement('div');
          sk.className = 'card skeleton skeleton-card';
          sk.setAttribute('aria-hidden', 'true');
          grid.appendChild(sk);
          skels.push(sk);
        }
      }
      fetch(url, { credentials: 'same-origin' })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          skels.forEach(function (el) {
            if (el.parentNode) el.parentNode.removeChild(el);
          });
          if (grid && data.html) {
            var wrap = document.createElement('div');
            wrap.innerHTML = data.html.trim();
            while (wrap.firstChild) {
              grid.appendChild(wrap.firstChild);
            }
            if (typeof AOS !== 'undefined') {
              AOS.refresh();
            }
          }
          loadMore.setAttribute('data-page', String(next));
          if (!data.hasMore) {
            loadMore.remove();
          } else {
            loadMore.disabled = false;
            loadMore.textContent = 'Muat lebih banyak';
          }
        })
        .catch(function () {
          loadMore.disabled = false;
          loadMore.textContent = 'Coba lagi';
        });
    });
  }

  function postJson(url, body) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
      credentials: 'same-origin',
    }).then(function (r) {
      return r.json();
    });
  }

  var btnLike = document.getElementById('btn-like');
  if (btnLike) {
    btnLike.addEventListener('click', function () {
      if (isStaticHosting) return;
      var id = parseInt(btnLike.getAttribute('data-article'), 10);
      postJson(apiUrl('api/toggle-like.php'), { article_id: id }).then(function (data) {
        if (!data.ok) return;
        btnLike.setAttribute('data-active', data.active ? '1' : '0');
        var lbl = document.getElementById('like-label');
        if (lbl) lbl.textContent = data.active ? 'Disukai' : 'Suka';
        var c = document.getElementById('like-count');
        if (c) c.textContent = data.count;
      });
    });
  }

  var btnBm = document.getElementById('btn-bookmark');
  if (btnBm) {
    btnBm.addEventListener('click', function () {
      if (isStaticHosting) return;
      var id = parseInt(btnBm.getAttribute('data-article'), 10);
      postJson(apiUrl('api/toggle-bookmark.php'), { article_id: id }).then(function (data) {
        if (!data.ok) return;
        btnBm.setAttribute('data-active', data.active ? '1' : '0');
        btnBm.textContent = data.active ? 'Tersimpan' : 'Simpan';
      });
    });
  }

  var copyBtn = document.getElementById('copy-link');
  if (copyBtn && navigator.clipboard) {
    copyBtn.addEventListener('click', function () {
      var u = copyBtn.getAttribute('data-url');
      navigator.clipboard.writeText(u).then(function () {
        copyBtn.textContent = 'Tersalin!';
        setTimeout(function () {
          copyBtn.textContent = 'Salin link';
        }, 2000);
      });
    });
  }

  var cform = document.getElementById('comment-form');
  if (cform) {
    cform.addEventListener('submit', function (e) {
      e.preventDefault();
      var msg = document.getElementById('comment-msg');
      if (isStaticHosting) {
        msg.textContent = 'Mode statis: komentar aktif saat dihosting PHP.';
        return;
      }
      msg.textContent = 'Mengirim…';
      var fd = new FormData(cform);
      fetch(apiUrl('api/comment.php'), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          if (!data.ok) {
            msg.textContent = data.error || 'Gagal';
            return;
          }
          msg.textContent = 'Komentar terkirim.';
          cform.reset();
          var list = document.getElementById('comment-list');
          var nc = document.getElementById('no-comments');
          if (nc) nc.remove();
          var div = document.createElement('div');
          div.className = 'comment-item';
          div.innerHTML =
            '<div class="comment-meta"><strong>' +
            escapeHtml(data.name) +
            '</strong> · ' +
            escapeHtml(data.time) +
            '</div><div>' +
            escapeHtml(data.text).replace(/\n/g, '<br>') +
            '</div>';
          list.insertBefore(div, list.firstChild);
        })
        .catch(function () {
          msg.textContent = 'Gagal mengirim.';
        });
    });
  }

  function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }
})();
