(function () {
  'use strict';

  var DATA_URL = 'data/site-data.json';
  var LS_USER_KEY = 'wc_current_user';
  var LS_COMMENTS_KEY = 'wc_comments';

  function $(id) {
    return document.getElementById(id);
  }

  function esc(s) {
    return String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function fmtDate(s) {
    var d = new Date(String(s).replace(' ', 'T'));
    if (isNaN(d.getTime())) return String(s || '');
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function fmtDateTime(s) {
    var d = new Date(String(s).replace(' ', 'T'));
    if (isNaN(d.getTime())) return String(s || '');
    return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  }

  function excerpt(text, max) {
    var t = String(text || '').replace(/<[^>]+>/g, '').trim();
    if (t.length <= max) return t;
    return t.slice(0, max - 1) + '…';
  }

  function q(name) {
    return new URLSearchParams(window.location.search).get(name) || '';
  }

  function loadSessionUser() {
    try {
      return JSON.parse(localStorage.getItem(LS_USER_KEY) || 'null');
    } catch (_e) {
      return null;
    }
  }

  function saveSessionUser(user) {
    localStorage.setItem(LS_USER_KEY, JSON.stringify(user));
  }

  function logout() {
    localStorage.removeItem(LS_USER_KEY);
    window.location.href = 'index.html';
  }

  function loadLocalComments() {
    try {
      return JSON.parse(localStorage.getItem(LS_COMMENTS_KEY) || '[]');
    } catch (_e) {
      return [];
    }
  }

  function saveLocalComments(items) {
    localStorage.setItem(LS_COMMENTS_KEY, JSON.stringify(items));
  }

  function mergeComments(seed, local) {
    return seed.concat(local);
  }

  function articleCard(a, catName, authorName) {
    return (
      '<article class="card">' +
      '<div class="card-thumb"><img src="' + esc(a.thumbnail) + '" alt="" loading="lazy"></div>' +
      '<div class="card-body">' +
      '<div class="card-meta">' + esc(catName) + ' · ' + esc(authorName) + '</div>' +
      '<h3 class="card-title"><a href="article.html?slug=' + encodeURIComponent(a.slug) + '">' + esc(a.judul) + '</a></h3>' +
      '<p class="card-excerpt">' + esc(a.excerpt || excerpt(a.konten, 120)) + '</p>' +
      '</div></article>'
    );
  }

  function renderAuthNav() {
    var user = loadSessionUser();
    var nav = $('auth-links');
    if (!nav) return;
    if (!user) {
      nav.innerHTML = '<a href="login.html">Masuk</a>';
      return;
    }
    var adminLink = user.role === 'admin' ? '<a href="admin.html">Admin</a>' : '';
    nav.innerHTML = adminLink + '<a href="author.html?id=' + user.id + '">Profil</a><a href="#" id="logout-link">Keluar</a>';
    var out = $('logout-link');
    if (out) {
      out.addEventListener('click', function (e) {
        e.preventDefault();
        logout();
      });
    }
  }

  function enrich(data) {
    var userById = {};
    var catById = {};
    data.users.forEach(function (u) {
      userById[u.id] = u;
    });
    data.categories.forEach(function (c) {
      catById[c.id] = c;
    });
    data.articles.forEach(function (a) {
      a.author = userById[a.author_id] || null;
      a.category = catById[a.kategori_id] || null;
    });
    return data;
  }

  function renderHome(data) {
    var featured = data.articles.filter(function (a) { return Number(a.featured) === 1; }).slice(0, 6);
    var latest = data.articles.slice().sort(function (a, b) { return String(b.tanggal_publish).localeCompare(String(a.tanggal_publish)); }).slice(0, 6);
    var trending = data.articles.slice().sort(function (a, b) { return Number(b.views) - Number(a.views); }).slice(0, 5);

    $('featured-grid').innerHTML = featured.map(function (a) {
      return articleCard(a, a.category ? a.category.nama_kategori : '-', a.author ? a.author.nama : '-');
    }).join('');

    $('latest-grid').innerHTML = latest.map(function (a) {
      return articleCard(a, a.category ? a.category.nama_kategori : '-', a.author ? a.author.nama : '-');
    }).join('');

    $('cat-pills').innerHTML = data.categories.map(function (c) {
      return '<a class="cat-pill" href="category.html?slug=' + encodeURIComponent(c.slug) + '">' + esc(c.nama_kategori) + '</a>';
    }).join('');

    $('trending-list').innerHTML = trending.map(function (a) {
      return '<li style="margin-bottom:0.5rem"><a href="article.html?slug=' + encodeURIComponent(a.slug) + '">' + esc(a.judul) + '</a><span class="muted"> — ' + Number(a.views) + ' views</span></li>';
    }).join('');
  }

  function renderArticles(data) {
    var cat = q('cat');
    var keyword = q('q').toLowerCase();
    var filtered = data.articles.slice().sort(function (a, b) { return String(b.tanggal_publish).localeCompare(String(a.tanggal_publish)); });
    if (cat) filtered = filtered.filter(function (a) { return a.category && a.category.slug === cat; });
    if (keyword) filtered = filtered.filter(function (a) { return a.judul.toLowerCase().indexOf(keyword) >= 0 || String(a.konten).toLowerCase().indexOf(keyword) >= 0; });

    var catSel = $('cat-select');
    if (catSel) {
      catSel.innerHTML = '<option value="">Semua</option>' + data.categories.map(function (c) {
        return '<option value="' + esc(c.slug) + '"' + (c.slug === cat ? ' selected' : '') + '>' + esc(c.nama_kategori) + '</option>';
      }).join('');
      catSel.addEventListener('change', function () {
        var u = new URL(window.location.href);
        if (catSel.value) u.searchParams.set('cat', catSel.value); else u.searchParams.delete('cat');
        window.location.href = u.pathname + u.search;
      });
    }

    $('articles-grid').innerHTML = filtered.map(function (a) {
      return articleCard(a, a.category ? a.category.nama_kategori : '-', a.author ? a.author.nama : '-');
    }).join('');
    if (filtered.length === 0) $('articles-grid').innerHTML = '<p class="muted">Tidak ada artikel yang cocok.</p>';
  }

  function renderArticle(data) {
    var slug = q('slug');
    var article = data.articles.find(function (a) { return a.slug === slug; }) || data.articles[0];
    if (!article) return;
    var comments = mergeComments(data.comments, loadLocalComments()).filter(function (c) { return Number(c.article_id) === Number(article.id); });

    $('article-title').textContent = article.judul;
    $('article-meta').textContent = (article.category ? article.category.nama_kategori : '-') + ' · ' + fmtDate(article.tanggal_publish) + ' · ' + Number(article.views) + ' views';
    $('article-thumb').src = article.thumbnail;
    $('article-content').innerHTML = article.konten;
    $('article-author').textContent = article.author ? article.author.nama : '-';

    var list = $('comment-list');
    list.innerHTML = comments.map(function (c) {
      return '<div class="comment-item"><div class="comment-meta"><strong>' + esc(c.name) + '</strong> · ' + esc(fmtDateTime(c.time)) + '</div><div>' + esc(c.text) + '</div></div>';
    }).join('');
    if (!comments.length) list.innerHTML = '<p class="muted">Belum ada komentar.</p>';

    var form = $('comment-form');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var name = ($('guest_name').value || '').trim();
        var text = ($('comment_text').value || '').trim();
        var user = loadSessionUser();
        var authorName = user ? user.nama : name;
        if (!authorName || !text) return;
        var local = loadLocalComments();
        local.push({
          article_id: article.id,
          name: authorName,
          text: text,
          time: new Date().toISOString().replace('T', ' ').slice(0, 19)
        });
        saveLocalComments(local);
        window.location.reload();
      });
    }
  }

  function renderCategory(data) {
    var slug = q('slug');
    var category = data.categories.find(function (c) { return c.slug === slug; }) || data.categories[0];
    var items = data.articles.filter(function (a) { return a.category && a.category.slug === category.slug; });
    $('category-title').textContent = category.nama_kategori;
    $('category-grid').innerHTML = items.map(function (a) {
      return articleCard(a, a.category ? a.category.nama_kategori : '-', a.author ? a.author.nama : '-');
    }).join('');
    if (!items.length) $('category-grid').innerHTML = '<p class="muted">Belum ada artikel.</p>';
  }

  function renderAuthor(data) {
    var id = Number(q('id') || 0);
    var user = data.users.find(function (u) { return u.id === id; }) || data.users[1];
    var items = data.articles.filter(function (a) { return Number(a.author_id) === Number(user.id); });
    $('author-name').textContent = user.nama;
    $('author-role').textContent = user.role;
    $('author-bio').textContent = user.bio;
    $('author-avatar').src = user.foto;
    $('author-grid').innerHTML = items.map(function (a) {
      return articleCard(a, a.category ? a.category.nama_kategori : '-', user.nama);
    }).join('');
  }

  function renderLogin(data) {
    var form = $('login-form');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var userInput = ($('user').value || '').trim();
      var passInput = ($('password').value || '').trim();
      var found = data.users.find(function (u) { return u.user === userInput && u.password === passInput; });
      var msg = $('login-msg');
      if (!found) {
        msg.textContent = 'User atau password salah.';
        return;
      }
      saveSessionUser({ id: found.id, nama: found.nama, role: found.role, user: found.user });
      window.location.href = found.role === 'admin' ? 'admin.html' : 'index.html';
    });
  }

  function renderAdmin(data) {
    var current = loadSessionUser();
    if (!current || current.role !== 'admin') {
      window.location.href = 'login.html';
      return;
    }
    var localComments = loadLocalComments();
    var allComments = mergeComments(data.comments, localComments);
    $('kpi-articles').textContent = String(data.articles.length);
    $('kpi-users').textContent = String(data.users.length);
    $('kpi-comments').textContent = String(allComments.length);
    $('kpi-subs').textContent = String(Number(localStorage.getItem('wc_newsletter_count') || '0'));

    $('admin-user-name').textContent = current.nama;
    $('admin-date').textContent = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

    var latest = data.articles.slice().sort(function (a, b) { return String(b.tanggal_publish).localeCompare(String(a.tanggal_publish)); }).slice(0, 5);
    $('admin-latest-articles').innerHTML = latest.map(function (a) {
      return '<a class="admin-list-item" href="article.html?slug=' + encodeURIComponent(a.slug) + '"><div><strong>' + esc(a.judul) + '</strong><p class="muted">oleh ' + esc(a.author ? a.author.nama : '-') + '</p></div><small class="muted">' + esc(fmtDate(a.tanggal_publish)) + '</small></a>';
    }).join('');
  }

  function bindStaticForms() {
    var nf = $('newsletter-form');
    if (nf) {
      nf.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = $('newsletter-msg');
        var current = Number(localStorage.getItem('wc_newsletter_count') || '0');
        localStorage.setItem('wc_newsletter_count', String(current + 1));
        msg.textContent = 'Berhasil berlangganan (mode statis).';
        nf.reset();
      });
    }
    var cf = $('contact-form');
    if (cf) {
      cf.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = $('contact-msg');
        msg.textContent = 'Pesan tersimpan di browser (mode statis).';
        cf.reset();
      });
    }
  }

  function route(data) {
    var page = document.body.getAttribute('data-page');
    renderAuthNav();
    bindStaticForms();
    if (page === 'home') renderHome(data);
    if (page === 'articles') renderArticles(data);
    if (page === 'article') renderArticle(data);
    if (page === 'category') renderCategory(data);
    if (page === 'author') renderAuthor(data);
    if (page === 'login') renderLogin(data);
    if (page === 'admin') renderAdmin(data);
  }

  fetch(DATA_URL)
    .then(function (r) { return r.json(); })
    .then(function (data) { route(enrich(data)); })
    .catch(function () {
      var box = $('page-error');
      if (box) box.textContent = 'Gagal memuat data statis.';
    });
})();
