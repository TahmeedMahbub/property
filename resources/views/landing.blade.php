@php($seo = $seo ?? \App\Support\LandingSeo::make(request()))
@php($lang = $seo['lang'] ?? 'bn')
<!DOCTYPE html>
<html lang="{{ $seo['htmlLocale'] }}" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="keywords" content="{{ $seo['keywords'] }}">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="author" content="{{ $seo['brand'] }}">
<meta name="theme-color" content="#18223B">
<link rel="canonical" href="{{ $seo['canonical'] }}">
@foreach ($seo['alternates'] as $hreflang => $href)
<link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
@endforeach
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $seo['brand'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:image" content="{{ $seo['image'] }}">
<meta property="og:image:alt" content="{{ $seo['brand'] }} dashboard preview">
<meta property="og:locale" content="{{ str_replace('-', '_', $seo['htmlLocale']) }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['image'] }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round&display=swap" rel="stylesheet">
<script type="application/ld+json">@json($seo['jsonLd'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
<style>
:root{
  --primary:#18223B;
  --primary-light:#1e2d4d;
  --primary-dark:#0f1628;
  --accent:#F59E0B;
  --accent-light:#FFFBEB;
  --accent-dark:#D97706;
  --gold:#F59E0B;
  --gold-light:#FFFBEB;
  --green:#10B981;
  --green-light:#ECFDF5;
  --red:#EF4444;
  --text:#1E293B;
  --text-2:#475569;
  --text-3:#94A3B8;
  --border:#E2E8F0;
  --bg:#F8FAFC;
  --white:#FFFFFF;
  --shadow:0 4px 24px rgba(24,34,59,.08);
  --shadow-md:0 8px 40px rgba(24,34,59,.12);
  --shadow-lg:0 20px 60px rgba(24,34,59,.15);
  --radius:16px;
  --radius-sm:10px;
  --transition:all .3s cubic-bezier(.4,0,.2,1);
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'Hind Siliguri','Inter',sans-serif;color:var(--text);background:var(--white);font-size:16px;line-height:1.7;-webkit-font-smoothing:antialiased}
.en body,.en *{font-family:'Inter','Hind Siliguri',sans-serif}
img{max-width:100%;height:auto}
a{text-decoration:none;color:inherit}

/* NAV */
nav{position:sticky;top:0;z-index:999;background:rgba(255,255,255,.97);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid var(--border);padding:0 5%}
.nav-inner{max-width:1240px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:70px}
.logo{font-size:1.4rem;font-weight:800;color:var(--primary);display:flex;align-items:center;gap:8px}
.logo-mark{width:36px;height:36px;background:var(--primary);border-radius:10px;display:flex;align-items:center;justify-content:center}
.logo-mark .material-icons-round{color:#fff;font-size:20px}
.nav-links{display:flex;align-items:center;gap:6px}
.nav-links a{color:var(--text-2);font-size:.9rem;font-weight:500;padding:8px 16px;border-radius:8px;transition:var(--transition)}
.nav-links a:hover{background:var(--accent-light);color:var(--accent)}
.btn-nav{border:2px solid var(--primary)!important;color:var(--primary)!important;font-weight:600!important}
.btn-nav:hover{background:var(--primary)!important;color:#fff!important}
.btn-nav-cta{background:var(--primary)!important;color:#fff!important;border:none!important;box-shadow:0 2px 8px rgba(24,34,59,.2)}
.btn-nav-cta:hover{background:var(--primary-dark)!important;transform:translateY(-1px)}
.lang-switch{background:var(--bg);border:1px solid var(--border);padding:6px 14px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;transition:var(--transition);color:var(--text-2)}
.lang-switch:hover{border-color:var(--accent);color:var(--accent)}
.hamburger{display:none;background:none;border:none;cursor:pointer;padding:8px;flex-direction:column;gap:5px}
.hamburger span{display:block;width:24px;height:2.5px;background:var(--text);border-radius:2px;transition:var(--transition)}
.mobile-menu{display:none;flex-direction:column;gap:4px;padding:12px 0 16px;border-top:1px solid var(--border)}
.mobile-menu a{color:var(--text-2);font-size:1rem;font-weight:500;padding:11px 16px;border-radius:8px;display:block;transition:var(--transition)}
.mobile-menu a:hover{background:var(--accent-light);color:var(--accent)}

/* BUTTONS */
.btn-primary{background:var(--primary);color:#fff;padding:14px 32px;border-radius:12px;font-size:1rem;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:var(--transition);box-shadow:0 4px 16px rgba(24,34,59,.25)}
.btn-primary:hover{background:var(--primary-dark);transform:translateY(-2px);box-shadow:0 8px 24px rgba(24,34,59,.3)}
.btn-secondary{background:#fff;color:var(--primary);padding:14px 32px;border-radius:12px;font-size:1rem;font-weight:700;border:2px solid var(--primary);cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:var(--transition)}
.btn-secondary:hover{background:var(--primary);color:#fff;transform:translateY(-2px)}
.btn-accent{background:var(--accent);color:#fff;padding:14px 32px;border-radius:12px;font-size:1rem;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:var(--transition);box-shadow:0 4px 16px rgba(245,158,11,.25)}
.btn-accent:hover{background:var(--accent-dark);transform:translateY(-2px)}

/* HERO */
.hero{background:linear-gradient(160deg,var(--primary) 0%,#1e2d4d 50%,#243352 100%);padding:80px 5% 100px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-200px;right:-200px;width:600px;height:600px;background:radial-gradient(circle,rgba(245,158,11,.15) 0%,transparent 70%);border-radius:50%}
.hero::after{content:'';position:absolute;bottom:-150px;left:-150px;width:500px;height:500px;background:radial-gradient(circle,rgba(245,158,11,.08) 0%,transparent 70%);border-radius:50%}
.hero-inner{max-width:1240px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;position:relative;z-index:1}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(245,158,11,.12);color:#FCD34D;border:1px solid rgba(245,158,11,.25);padding:8px 18px;border-radius:50px;font-size:.85rem;font-weight:600;margin-bottom:22px}
.hero-badge .dot{width:8px;height:8px;background:#F59E0B;border-radius:50%;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.4)}}
.hero h1{font-size:clamp(2rem,4.2vw,3.2rem);font-weight:800;line-height:1.2;color:#fff;margin-bottom:20px}
.hero h1 .highlight{color:var(--gold);position:relative}
.hero-sub{font-size:1.1rem;color:rgba(255,255,255,.75);margin-bottom:34px;max-width:520px;line-height:1.8}
.hero-btns{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:40px}
.hero .btn-primary{background:var(--accent);box-shadow:0 4px 16px rgba(245,158,11,.3)}
.hero .btn-primary:hover{background:var(--accent-dark)}
.hero .btn-secondary{border-color:rgba(255,255,255,.3);color:#fff}
.hero .btn-secondary:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.5)}
.hero-stats{display:flex;gap:30px;flex-wrap:wrap}
.hero-stat{text-align:center}
.hero-stat .num{font-size:1.8rem;font-weight:800;color:#fff}
.hero-stat .label{font-size:.8rem;color:rgba(255,255,255,.6)}
.hero-visual{position:relative;display:flex;justify-content:center}
.hero-mockup{width:100%;max-width:560px;border-radius:16px;box-shadow:0 30px 80px rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);background:var(--primary-light);aspect-ratio:16/10;display:flex;align-items:center;justify-content:center;overflow:hidden}
.hero-mockup-placeholder{color:rgba(255,255,255,.3);font-size:.9rem;text-align:center;padding:40px}
.hero-mockup-placeholder .material-icons-round{font-size:60px;display:block;margin-bottom:10px;color:rgba(59,130,246,.4)}

/* SECTIONS */
section{padding:80px 5%}
.section-inner{max-width:1240px;margin:0 auto}
.section-tag{display:inline-flex;align-items:center;gap:8px;background:var(--accent-light);color:var(--accent);padding:7px 18px;border-radius:50px;font-size:.82rem;font-weight:700;margin-bottom:14px}
.section-tag .material-icons-round{font-size:16px}
.section-title{font-size:clamp(1.6rem,3.5vw,2.5rem);font-weight:800;margin-bottom:14px;line-height:1.25;color:var(--primary)}
.section-sub{font-size:1.05rem;color:var(--text-2);max-width:600px;line-height:1.7}
.text-center{text-align:center}
.text-center .section-sub{margin:0 auto}
.section-bg{background:var(--bg)}

/* FEATURES */
.features-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px;margin-top:50px}
.feature-card{background:#fff;border-radius:var(--radius);padding:30px;border:1px solid var(--border);transition:var(--transition);position:relative;overflow:hidden}
.feature-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),var(--gold));border-radius:3px 3px 0 0;transform:scaleX(0);transform-origin:left;transition:.4s}
.feature-card:hover::before{transform:scaleX(1)}
.feature-card:hover{box-shadow:var(--shadow-md);transform:translateY(-5px);border-color:rgba(24,34,59,.1)}
.feat-icon{width:56px;height:56px;border-radius:14px;background:var(--accent-light);display:flex;align-items:center;justify-content:center;margin-bottom:18px;border:1px solid rgba(245,158,11,.15);color:var(--accent)}
.feat-icon .material-icons-round{font-size:28px}
.feature-card h3{font-size:1.1rem;font-weight:700;margin-bottom:10px;color:var(--primary)}
.feature-card p{font-size:.9rem;color:var(--text-2);line-height:1.6}

/* HOW IT WORKS */
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:30px;margin-top:50px;counter-reset:step}
.step{text-align:center;position:relative;padding:30px 20px}
.step::before{counter-increment:step;content:counter(step);width:50px;height:50px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;margin:0 auto 20px}
.step h4{font-size:1rem;font-weight:700;margin-bottom:8px;color:var(--primary)}
.step p{font-size:.88rem;color:var(--text-2)}

/* PRICING */
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;margin-top:50px}
.price-card{background:#fff;border-radius:var(--radius);padding:36px 30px;border:1px solid var(--border);transition:var(--transition);position:relative;display:flex;flex-direction:column}
.price-card:hover{box-shadow:var(--shadow-md);transform:translateY(-5px)}
.price-card.popular{border:2px solid var(--accent);box-shadow:var(--shadow-md)}
.price-card.popular::after{content:'';position:absolute;top:-2px;left:20%;right:20%;height:3px;background:var(--accent);border-radius:0 0 4px 4px}
.popular-badge{position:absolute;top:16px;right:16px;background:var(--accent);color:#fff;padding:4px 12px;border-radius:50px;font-size:.72rem;font-weight:700;text-transform:uppercase}
.price-name{font-size:1.1rem;font-weight:700;color:var(--primary);margin-bottom:6px}
.price-amount{font-size:2.5rem;font-weight:800;color:var(--primary);margin-bottom:4px}
.price-amount span{font-size:.9rem;font-weight:500;color:var(--text-3)}
.price-desc{font-size:.88rem;color:var(--text-2);margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border)}
.price-features{list-style:none;margin-bottom:30px;flex:1}
.price-features li{display:flex;align-items:center;gap:10px;padding:7px 0;font-size:.88rem;color:var(--text-2)}
.price-features li .material-icons-round{font-size:18px;color:var(--green)}
.price-features li.disabled{color:var(--text-3)}
.price-features li.disabled .material-icons-round{color:var(--text-3)}
.price-card .btn-primary{width:100%;justify-content:center}
.price-card .btn-secondary{width:100%;justify-content:center}

/* STATS */
.stats-section{background:var(--primary);padding:60px 5%}
.stats-grid{max-width:1240px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:40px;text-align:center}
.stat-item .stat-num{font-size:2.5rem;font-weight:800;color:#fff}
.stat-item .stat-label{font-size:.9rem;color:rgba(255,255,255,.6);margin-top:4px}

/* TESTIMONIALS */
.testimonials-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;margin-top:50px}
.testimonial-card{background:#fff;border-radius:var(--radius);padding:30px;border:1px solid var(--border);transition:var(--transition)}
.testimonial-card:hover{box-shadow:var(--shadow-md);transform:translateY(-3px)}
.testimonial-stars{color:var(--gold);margin-bottom:14px}
.testimonial-text{font-size:.92rem;color:var(--text-2);line-height:1.7;margin-bottom:20px;font-style:italic}
.testimonial-author{display:flex;align-items:center;gap:12px}
.testimonial-avatar{width:44px;height:44px;border-radius:50%;background:var(--accent-light);display:flex;align-items:center;justify-content:center;color:var(--accent);font-weight:700;font-size:.9rem}
.testimonial-info h5{font-size:.88rem;font-weight:700;color:var(--primary)}
.testimonial-info p{font-size:.78rem;color:var(--text-3)}

/* FAQ */
.faq-list{max-width:760px;margin:50px auto 0}
.faq-item{border:1px solid var(--border);border-radius:12px;margin-bottom:12px;overflow:hidden;transition:var(--transition)}
.faq-item:hover{border-color:rgba(245,158,11,.3)}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;cursor:pointer;font-weight:600;font-size:.95rem;color:var(--primary);background:#fff;transition:var(--transition)}
.faq-q:hover{background:var(--bg)}
.faq-q .material-icons-round{font-size:22px;color:var(--text-3);transition:var(--transition)}
.faq-item.open .faq-q .material-icons-round{transform:rotate(180deg);color:var(--accent)}
.faq-a{max-height:0;overflow:hidden;transition:max-height .3s ease;background:#fff}
.faq-a-inner{padding:0 24px 18px;font-size:.9rem;color:var(--text-2);line-height:1.7}
.faq-item.open .faq-a{max-height:300px}

/* CTA */
.cta-section{background:linear-gradient(135deg,var(--primary),#1e2d4d);padding:80px 5%;text-align:center;color:#fff}
.cta-section h2{font-size:clamp(1.6rem,3.5vw,2.4rem);font-weight:800;margin-bottom:14px}
.cta-section p{font-size:1.05rem;color:rgba(255,255,255,.7);margin-bottom:30px;max-width:500px;margin-left:auto;margin-right:auto}
.cta-section .btn-accent{font-size:1.1rem;padding:16px 40px}

/* FOOTER */
footer{background:var(--primary-dark);padding:50px 5% 30px;color:rgba(255,255,255,.7)}
.footer-inner{max-width:1240px;margin:0 auto}
.footer-top{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;padding-bottom:30px;border-bottom:1px solid rgba(255,255,255,.08)}
.footer-brand{font-size:1.3rem;font-weight:800;color:#fff;margin-bottom:12px}
.footer-desc{font-size:.88rem;line-height:1.7;max-width:300px}
.footer-col h4{color:#fff;font-size:.9rem;font-weight:700;margin-bottom:14px}
.footer-col a{display:block;font-size:.85rem;padding:4px 0;transition:var(--transition)}
.footer-col a:hover{color:var(--accent)}
.footer-bottom{display:flex;justify-content:space-between;align-items:center;padding-top:24px;font-size:.82rem}

/* RESPONSIVE */
@media(max-width:1024px){
  .hero-inner{grid-template-columns:1fr;text-align:center}
  .hero-sub{margin:0 auto 34px}
  .hero-btns{justify-content:center}
  .hero-stats{justify-content:center}
  .hero-visual{margin-top:40px}
  .footer-top{grid-template-columns:1fr 1fr}
}
@media(max-width:768px){
  nav .nav-links{display:none}
  .hamburger{display:flex}
  .mobile-menu.active{display:flex}
  .features-grid,.pricing-grid,.testimonials-grid{grid-template-columns:1fr}
  .stats-grid{grid-template-columns:1fr 1fr}
  .footer-top{grid-template-columns:1fr}
  .footer-bottom{flex-direction:column;gap:10px;text-align:center}
  .hero{padding:50px 5% 70px}
  section{padding:60px 5%}
}
@media(max-width:480px){
  .hero h1{font-size:1.8rem}
  .hero-stats{gap:20px}
  .price-card{padding:24px 20px}
  .steps{grid-template-columns:1fr}
}
</style>
</head>
<body class="{{ $lang === 'en' ? 'en' : '' }}">

<!-- NAV -->
<nav>
  <div class="nav-inner">
    <a href="/" class="logo">
      <span class="logo-mark"><span class="material-icons-round">apartment</span></span>
      {{ $lang === 'bn' ? 'হিসাবিজ প্রপার্টি' : 'Hishabiz Property' }}
    </a>
    <div class="nav-links">
      <a href="#features">{{ $lang === 'bn' ? 'ফিচার' : 'Features' }}</a>
      <a href="#pricing">{{ $lang === 'bn' ? 'মূল্য' : 'Pricing' }}</a>
      <a href="#faq">{{ $lang === 'bn' ? 'জিজ্ঞাসা' : 'FAQ' }}</a>
      <a href="?lang={{ $lang === 'bn' ? 'en' : 'bn' }}" class="lang-switch">{{ $lang === 'bn' ? 'EN' : 'বাং' }}</a>
      <a href="/login" class="btn-nav">{{ $lang === 'bn' ? 'লগইন' : 'Login' }}</a>
      <a href="/register" class="btn-nav-cta">{{ $lang === 'bn' ? 'ফ্রি শুরু করুন' : 'Start Free' }}</a>
    </div>
    <button class="hamburger" onclick="document.querySelector('.mobile-menu').classList.toggle('active')" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div class="mobile-menu">
    <a href="#features">{{ $lang === 'bn' ? 'ফিচার' : 'Features' }}</a>
    <a href="#pricing">{{ $lang === 'bn' ? 'মূল্য' : 'Pricing' }}</a>
    <a href="#faq">{{ $lang === 'bn' ? 'জিজ্ঞাসা' : 'FAQ' }}</a>
    <a href="?lang={{ $lang === 'bn' ? 'en' : 'bn' }}" class="lang-switch" style="margin:8px 16px;width:fit-content">{{ $lang === 'bn' ? 'EN' : 'বাং' }}</a>
    <a href="/login" style="margin:4px 16px;width:fit-content;padding:10px 20px;border-radius:8px;border:2px solid var(--primary);font-weight:600;color:var(--primary)">{{ $lang === 'bn' ? 'লগইন' : 'Login' }}</a>
    <a href="/register" style="margin:4px 16px;width:fit-content;padding:10px 20px;border-radius:8px;background:var(--primary);color:#fff;font-weight:600">{{ $lang === 'bn' ? 'ফ্রি শুরু করুন' : 'Start Free' }}</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div>
      <div class="hero-badge"><span class="dot"></span>{{ $lang === 'bn' ? 'বাংলাদেশের #১ প্রপার্টি সফটওয়্যার' : '#1 Property Software in Bangladesh' }}</div>
      <h1>
        @if($lang === 'bn')
          আপনার <span class="highlight">রিয়েল এস্টেট</span> ব্যবসা এখন সম্পূর্ণ ডিজিটাল
        @else
          Your <span class="highlight">Real Estate</span> Business, Fully Digital
        @endif
      </h1>
      <p class="hero-sub">
        @if($lang === 'bn')
          প্রজেক্ট, বিল্ডিং, ফ্লোর, ইউনিট, বিনিয়োগকারী, গ্রাহক — সবকিছু এক ড্যাশবোর্ডে। সহজে পরিচালনা করুন, দ্রুত সিদ্ধান্ত নিন।
        @else
          Projects, buildings, floors, units, investors, customers — everything in one dashboard. Manage easily, decide faster.
        @endif
      </p>
      <div class="hero-btns">
        <a href="/register" class="btn-primary">{{ $lang === 'bn' ? 'ফ্রি শুরু করুন' : 'Start For Free' }} <span class="material-icons-round">arrow_forward</span></a>
        <a href="/login" class="btn-secondary">{{ $lang === 'bn' ? 'ফিচার দেখুন' : 'See Features' }}</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><div class="num">{{ $lang === 'bn' ? '৫০০+' : '500+' }}</div><div class="label">{{ $lang === 'bn' ? 'সক্রিয় কোম্পানি' : 'Active Companies' }}</div></div>
        <div class="hero-stat"><div class="num">{{ $lang === 'bn' ? '১২,০০০+' : '12,000+' }}</div><div class="label">{{ $lang === 'bn' ? 'ইউনিট পরিচালিত' : 'Units Managed' }}</div></div>
        <div class="hero-stat"><div class="num">{{ $lang === 'bn' ? '৯৯.৯%' : '99.9%' }}</div><div class="label">{{ $lang === 'bn' ? 'আপটাইম' : 'Uptime' }}</div></div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-mockup">
        <div class="hero-mockup-placeholder">
          <span class="material-icons-round">dashboard</span>
          {{ $lang === 'bn' ? 'ড্যাশবোর্ড প্রিভিউ' : 'Dashboard Preview' }}
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section id="features" class="section-bg">
  <div class="section-inner text-center">
    <div class="section-tag"><span class="material-icons-round">auto_awesome</span>{{ $lang === 'bn' ? 'ফিচারসমূহ' : 'Features' }}</div>
    <h2 class="section-title">{{ $lang === 'bn' ? 'সব কিছু এক প্ল্যাটফর্মে' : 'Everything in One Platform' }}</h2>
    <p class="section-sub">{{ $lang === 'bn' ? 'প্রপার্টি ব্যবসা পরিচালনার জন্য যা যা দরকার, সব এখানে।' : 'Everything you need to manage your property business.' }}</p>
  </div>
  <div class="section-inner">
    <div class="features-grid">
      <?php
      $features = $lang === 'bn' ? [
        ['icon'=>'domain','title'=>'প্রজেক্ট ম্যানেজমেন্ট','desc'=>'একাধিক প্রজেক্ট তৈরি, ট্র্যাক ও পরিচালনা করুন। বাজেট, টাইমলাইন, স্ট্যাটাস সব দেখুন এক জায়গায়।'],
        ['icon'=>'apartment','title'=>'বিল্ডিং ও ফ্লোর','desc'=>'প্রতিটি প্রজেক্টে বিল্ডিং, ফ্লোর, এবং ইউনিট যোগ করুন। হায়ারার্কি মেইনটেইন করুন সহজে।'],
        ['icon'=>'home_work','title'=>'ইউনিট ট্র্যাকিং','desc'=>'প্রতিটি ইউনিটের সাইজ, দাম, স্ট্যাটাস (Available, Booked, Sold) ট্র্যাক করুন রিয়েল-টাইমে।'],
        ['icon'=>'people','title'=>'বিনিয়োগকারী ব্যবস্থাপনা','desc'=>'প্রজেক্টভিত্তিক বিনিয়োগকারী, বিনিয়োগের পরিমাণ, শেয়ার পার্সেন্টেজ সহজেই পরিচালনা করুন।'],
        ['icon'=>'person_search','title'=>'গ্রাহক ডেটাবেজ','desc'=>'সব গ্রাহকের তথ্য, ক্রেডিট লিমিট, যোগাযোগ সংরক্ষণ করুন। Individual ও Business দুই ধরনেই।'],
        ['icon'=>'description','title'=>'ডকুমেন্ট ম্যানেজমেন্ট','desc'=>'ফোল্ডার, ক্যাটাগরি ভিত্তিক ডকুমেন্ট আপলোড ও ভার্সন কন্ট্রোল। প্রতিটি এন্টিটির সাথে সংযুক্ত।'],
      ] : [
        ['icon'=>'domain','title'=>'Project Management','desc'=>'Create, track, and manage multiple projects. View budgets, timelines, and status all in one place.'],
        ['icon'=>'apartment','title'=>'Buildings & Floors','desc'=>'Add buildings, floors, and units to each project. Maintain hierarchy effortlessly.'],
        ['icon'=>'home_work','title'=>'Unit Tracking','desc'=>'Track each unit size, price, and status (Available, Booked, Sold) in real-time.'],
        ['icon'=>'people','title'=>'Investor Management','desc'=>'Manage project-based investors, investment amounts, and share percentages easily.'],
        ['icon'=>'person_search','title'=>'Customer Database','desc'=>'Store all customer info, credit limits, contacts. Supports both Individual and Business types.'],
        ['icon'=>'description','title'=>'Document Management','desc'=>'Upload documents with folder/category structure and version control. Attach to any entity.'],
      ];
      ?>
      @foreach($features as $f)
      <div class="feature-card">
        <div class="feat-icon"><span class="material-icons-round">{{ $f['icon'] }}</span></div>
        <h3>{{ $f['title'] }}</h3>
        <p>{{ $f['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section>
  <div class="section-inner text-center">
    <div class="section-tag"><span class="material-icons-round">route</span>{{ $lang === 'bn' ? 'কিভাবে কাজ করে' : 'How It Works' }}</div>
    <h2 class="section-title">{{ $lang === 'bn' ? '৩ ধাপে শুরু করুন' : 'Get Started in 3 Steps' }}</h2>
    <p class="section-sub">{{ $lang === 'bn' ? 'মাত্র কয়েক মিনিটে আপনার প্রপার্টি ব্যবসা ডিজিটাল করুন।' : 'Digitize your property business in just a few minutes.' }}</p>
  </div>
  <div class="section-inner">
    <div class="steps">
      <div class="step">
        <h4>{{ $lang === 'bn' ? 'অ্যাকাউন্ট তৈরি করুন' : 'Create Account' }}</h4>
        <p>{{ $lang === 'bn' ? 'ফ্রি রেজিস্ট্রেশন করুন। কোনো ক্রেডিট কার্ড লাগবে না।' : 'Register for free. No credit card required.' }}</p>
      </div>
      <div class="step">
        <h4>{{ $lang === 'bn' ? 'কোম্পানি ও প্রজেক্ট সেটআপ' : 'Setup Company & Project' }}</h4>
        <p>{{ $lang === 'bn' ? 'আপনার কোম্পানি তৈরি করুন, প্রজেক্ট যোগ করুন, বিল্ডিং ও ইউনিট সেটআপ করুন।' : 'Create your company, add projects, setup buildings & units.' }}</p>
      </div>
      <div class="step">
        <h4>{{ $lang === 'bn' ? 'ব্যবসা পরিচালনা শুরু' : 'Start Managing' }}</h4>
        <p>{{ $lang === 'bn' ? 'গ্রাহক, বিনিয়োগকারী, বিক্রয় সব ট্র্যাক করুন এক ড্যাশবোর্ড থেকে।' : 'Track customers, investors, sales — all from one dashboard.' }}</p>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="stats-section">
  <div class="stats-grid">
    <div class="stat-item">
      <div class="stat-num">{{ $lang === 'bn' ? '৫০০+' : '500+' }}</div>
      <div class="stat-label">{{ $lang === 'bn' ? 'সক্রিয় কোম্পানি' : 'Active Companies' }}</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">{{ $lang === 'bn' ? '২,৫০০+' : '2,500+' }}</div>
      <div class="stat-label">{{ $lang === 'bn' ? 'প্রজেক্ট পরিচালিত' : 'Projects Managed' }}</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">{{ $lang === 'bn' ? '১২,০০০+' : '12,000+' }}</div>
      <div class="stat-label">{{ $lang === 'bn' ? 'ইউনিট ট্র্যাকিং' : 'Units Tracked' }}</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">{{ $lang === 'bn' ? '৳৫০০ কোটি+' : '৳500 Cr+' }}</div>
      <div class="stat-label">{{ $lang === 'bn' ? 'সম্পত্তি মূল্য পরিচালিত' : 'Property Value Managed' }}</div>
    </div>
  </div>
</section>

<!-- PRICING -->
<section id="pricing">
  <div class="section-inner text-center">
    <div class="section-tag"><span class="material-icons-round">sell</span>{{ $lang === 'bn' ? 'মূল্য পরিকল্পনা' : 'Pricing Plans' }}</div>
    <h2 class="section-title">{{ $lang === 'bn' ? 'আপনার প্রয়োজন অনুযায়ী প্ল্যান বেছে নিন' : 'Choose the Plan That Fits You' }}</h2>
    <p class="section-sub">{{ $lang === 'bn' ? 'ফ্রি দিয়ে শুরু করুন, যখন বাড়বে তখন আপগ্রেড করুন।' : 'Start free, upgrade when you grow.' }}</p>
  </div>
  <div class="section-inner">
    <div class="pricing-grid">
      {{-- FREE --}}
      <div class="price-card">
        <div class="price-name">{{ $lang === 'bn' ? 'ফ্রি' : 'Free' }}</div>
        <div class="price-amount">{{ $lang === 'bn' ? '৳০' : '৳0' }} <span>/{{ $lang === 'bn' ? 'মাস' : 'mo' }}</span></div>
        <div class="price-desc">{{ $lang === 'bn' ? 'ছোট ব্যবসার জন্য পারফেক্ট। সব বেসিক ফিচার ফ্রিতে।' : 'Perfect for small businesses. All basic features free.' }}</div>
        <ul class="price-features">
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? '১টি কোম্পানি' : '1 Company' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? '২টি প্রজেক্ট' : '2 Projects' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? '৫০টি ইউনিট' : '50 Units' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? '৩ জন টিম মেম্বার' : '3 Team Members' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'বেসিক রিপোর্ট' : 'Basic Reports' }}</li>
          <li class="disabled"><span class="material-icons-round">cancel</span>{{ $lang === 'bn' ? 'ডকুমেন্ট ম্যানেজমেন্ট' : 'Document Management' }}</li>
          <li class="disabled"><span class="material-icons-round">cancel</span>{{ $lang === 'bn' ? 'API অ্যাক্সেস' : 'API Access' }}</li>
        </ul>
        <a href="/register" class="btn-secondary">{{ $lang === 'bn' ? 'ফ্রি শুরু করুন' : 'Start Free' }}</a>
      </div>
      {{-- PRO --}}
      <div class="price-card popular">
        <span class="popular-badge">{{ $lang === 'bn' ? 'জনপ্রিয়' : 'Popular' }}</span>
        <div class="price-name">{{ $lang === 'bn' ? 'প্রো' : 'Pro' }}</div>
        <div class="price-amount">{{ $lang === 'bn' ? '৳৯৯৯' : '৳999' }} <span>/{{ $lang === 'bn' ? 'মাস' : 'mo' }}</span></div>
        <div class="price-desc">{{ $lang === 'bn' ? 'বাড়তি ব্যবসার জন্য। সব ফিচার আনলিমিটেড।' : 'For growing businesses. All features unlimited.' }}</div>
        <ul class="price-features">
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? '৫টি কোম্পানি' : '5 Companies' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'আনলিমিটেড প্রজেক্ট' : 'Unlimited Projects' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'আনলিমিটেড ইউনিট' : 'Unlimited Units' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? '১৫ জন টিম মেম্বার' : '15 Team Members' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'অ্যাডভান্সড রিপোর্ট' : 'Advanced Reports' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'ডকুমেন্ট ম্যানেজমেন্ট' : 'Document Management' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'ইমেইল সাপোর্ট' : 'Email Support' }}</li>
        </ul>
        <a href="/login" class="btn-primary">{{ $lang === 'bn' ? 'প্রো নিন' : 'Get Pro' }}</a>
      </div>
      {{-- ENTERPRISE --}}
      <div class="price-card">
        <div class="price-name">{{ $lang === 'bn' ? 'এন্টারপ্রাইজ' : 'Enterprise' }}</div>
        <div class="price-amount">{{ $lang === 'bn' ? '৳২,৯৯৯' : '৳2,999' }} <span>/{{ $lang === 'bn' ? 'মাস' : 'mo' }}</span></div>
        <div class="price-desc">{{ $lang === 'bn' ? 'বড় প্রতিষ্ঠানের জন্য কাস্টম সলিউশন।' : 'Custom solution for large organizations.' }}</div>
        <ul class="price-features">
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'আনলিমিটেড কোম্পানি' : 'Unlimited Companies' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'আনলিমিটেড সবকিছু' : 'Unlimited Everything' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'আনলিমিটেড টিম মেম্বার' : 'Unlimited Team Members' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'API অ্যাক্সেস' : 'API Access' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'কাস্টম ব্র্যান্ডিং' : 'Custom Branding' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'ডেডিকেটেড সাপোর্ট' : 'Dedicated Support' }}</li>
          <li><span class="material-icons-round">check_circle</span>{{ $lang === 'bn' ? 'SLA গ্যারান্টি' : 'SLA Guarantee' }}</li>
        </ul>
        <a href="/login" class="btn-secondary">{{ $lang === 'bn' ? 'যোগাযোগ করুন' : 'Contact Us' }}</a>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section-bg">
  <div class="section-inner text-center">
    <div class="section-tag"><span class="material-icons-round">format_quote</span>{{ $lang === 'bn' ? 'গ্রাহকদের মতামত' : 'Testimonials' }}</div>
    <h2 class="section-title">{{ $lang === 'bn' ? 'তারা কি বলছেন' : 'What They Say' }}</h2>
    <p class="section-sub">{{ $lang === 'bn' ? 'বাংলাদেশের শত শত প্রপার্টি ব্যবসা আমাদের ব্যবহার করছে।' : 'Hundreds of property businesses in Bangladesh trust us.' }}</p>
  </div>
  <div class="section-inner">
    <div class="testimonials-grid">
      <?php
      $testimonials = $lang === 'bn' ? [
        ['text'=>'হিসাবিজ প্রপার্টি আমাদের পুরো ব্যবসাকে ডিজিটাল করেছে। আগে এক্সেলে হিসাব রাখতাম, এখন সব অটোমেটিক।','name'=>'মো. রহিম আহমেদ','role'=>'MD, রহিম কনস্ট্রাকশন','avatar'=>'র'],
        ['text'=>'বিনিয়োগকারীদের তথ্য এবং ইউনিটের স্ট্যাটাস এখন ফোন থেকেই দেখতে পারি। অসাধারণ সফটওয়্যার!','name'=>'ফাতিমা বেগম','role'=>'ডিরেক্টর, গ্রীন হোমস','avatar'=>'ফ'],
        ['text'=>'কাস্টমার সার্ভিস অসাম। যেকোনো সমস্যায় ২ ঘণ্টার মধ্যে সমাধান পাই। হাইলি রেকমেন্ড করি।','name'=>'কামাল হাসান','role'=>'CEO, হাসান প্রপার্টিজ','avatar'=>'ক'],
      ] : [
        ['text'=>'Hishabiz Property digitized our entire business. We used to track in Excel, now everything is automated.','name'=>'Rahim Ahmed','role'=>'MD, Rahim Construction','avatar'=>'R'],
        ['text'=>'I can now check investor info and unit status from my phone. Amazing software!','name'=>'Fatima Begum','role'=>'Director, Green Homes','avatar'=>'F'],
        ['text'=>'Customer service is awesome. Any issue gets resolved within 2 hours. Highly recommend.','name'=>'Kamal Hasan','role'=>'CEO, Hasan Properties','avatar'=>'K'],
      ];
      ?>
      @foreach($testimonials as $t)
      <div class="testimonial-card">
        <div class="testimonial-stars">★★★★★</div>
        <p class="testimonial-text">"{{ $t['text'] }}"</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar">{{ $t['avatar'] }}</div>
          <div class="testimonial-info">
            <h5>{{ $t['name'] }}</h5>
            <p>{{ $t['role'] }}</p>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- FAQ -->
<section id="faq">
  <div class="section-inner text-center">
    <div class="section-tag"><span class="material-icons-round">help</span>{{ $lang === 'bn' ? 'সচরাচর জিজ্ঞাসা' : 'FAQ' }}</div>
    <h2 class="section-title">{{ $lang === 'bn' ? 'প্রশ্ন ও উত্তর' : 'Questions & Answers' }}</h2>
  </div>
  <div class="section-inner">
    <div class="faq-list">
      <?php
      $faqs = $lang === 'bn' ? [
        ['q'=>'ফ্রি প্ল্যানে কি কোনো লিমিটেশন আছে?','a'=>'ফ্রি প্ল্যানে ১টি কোম্পানি, ২টি প্রজেক্ট এবং ৫০টি ইউনিট পর্যন্ত ব্যবহার করতে পারবেন। বেসিক রিপোর্ট এবং টিম ম্যানেজমেন্ট ফিচার অন্তর্ভুক্ত।'],
        ['q'=>'আমি কি পরে প্ল্যান আপগ্রেড করতে পারব?','a'=>'অবশ্যই! যেকোনো সময় Pro বা Enterprise প্ল্যানে আপগ্রেড করতে পারবেন। আপনার ডেটা সম্পূর্ণ সুরক্ষিত থাকবে।'],
        ['q'=>'ডেটা কি নিরাপদ?','a'=>'হ্যাঁ, আমরা SSL এনক্রিপশন, ডেইলি ব্যাকআপ এবং সিকিউর সার্ভার ব্যবহার করি। আপনার ডেটা ১০০% সুরক্ষিত।'],
        ['q'=>'মোবাইল থেকে ব্যবহার করা যাবে?','a'=>'হ্যাঁ! আমাদের সফটওয়্যার সম্পূর্ণ রেসপন্সিভ। যেকোনো ডিভাইস থেকে (মোবাইল, ট্যাবলেট, ডেস্কটপ) ব্যবহার করতে পারবেন।'],
        ['q'=>'ট্রায়ালের জন্য ক্রেডিট কার্ড লাগবে?','a'=>'না, ফ্রি ট্রায়ালের জন্য কোনো ক্রেডিট কার্ড বা পেমেন্ট তথ্য প্রয়োজন নেই। সরাসরি শুরু করুন!'],
      ] : [
        ['q'=>'Are there any limitations on the Free plan?','a'=>'On the Free plan, you can use 1 company, 2 projects, and up to 50 units. Basic reports and team management features are included.'],
        ['q'=>'Can I upgrade my plan later?','a'=>'Absolutely! You can upgrade to Pro or Enterprise at any time. Your data will remain completely safe.'],
        ['q'=>'Is my data secure?','a'=>'Yes, we use SSL encryption, daily backups, and secure servers. Your data is 100% protected.'],
        ['q'=>'Can I use it on mobile?','a'=>'Yes! Our software is fully responsive. You can use it from any device (mobile, tablet, desktop).'],
        ['q'=>'Do I need a credit card for the trial?','a'=>'No, no credit card or payment info is needed for the free trial. Start right away!'],
      ];
      ?>
      @foreach($faqs as $i => $faq)
      <div class="faq-item{{ $i === 0 ? ' open' : '' }}">
        <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">
          {{ $faq['q'] }}
          <span class="material-icons-round">expand_more</span>
        </div>
        <div class="faq-a"><div class="faq-a-inner">{{ $faq['a'] }}</div></div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <h2>{{ $lang === 'bn' ? 'আজই শুরু করুন — সম্পূর্ণ ফ্রি!' : 'Start Today — Completely Free!' }}</h2>
  <p>{{ $lang === 'bn' ? 'হাজার হাজার রিয়েল এস্টেট ব্যবসা ইতোমধ্যে হিসাবিজ ব্যবহার করছে। আপনিও যোগ দিন।' : 'Thousands of real estate businesses already use Hishabiz. Join them today.' }}</p>
  <a href="/register" class="btn-accent">{{ $lang === 'bn' ? 'ফ্রি অ্যাকাউন্ট তৈরি করুন' : 'Create Free Account' }} <span class="material-icons-round">arrow_forward</span></a>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-top">
      <div>
        <div class="footer-brand">🏢 {{ $lang === 'bn' ? 'হিসাবিজ প্রপার্টি' : 'Hishabiz Property' }}</div>
        <p class="footer-desc">{{ $lang === 'bn' ? 'বাংলাদেশের সবচেয়ে সহজ ও শক্তিশালী রিয়েল এস্টেট ম্যানেজমেন্ট সফটওয়্যার।' : "Bangladesh's easiest and most powerful real estate management software." }}</p>
      </div>
      <div class="footer-col">
        <h4>{{ $lang === 'bn' ? 'পণ্য' : 'Product' }}</h4>
        <a href="#features">{{ $lang === 'bn' ? 'ফিচার' : 'Features' }}</a>
        <a href="#pricing">{{ $lang === 'bn' ? 'মূল্য' : 'Pricing' }}</a>
        <a href="#faq">{{ $lang === 'bn' ? 'FAQ' : 'FAQ' }}</a>
      </div>
      <div class="footer-col">
        <h4>{{ $lang === 'bn' ? 'কোম্পানি' : 'Company' }}</h4>
        <a href="#">{{ $lang === 'bn' ? 'আমাদের সম্পর্কে' : 'About Us' }}</a>
        <a href="#">{{ $lang === 'bn' ? 'ব্লগ' : 'Blog' }}</a>
        <a href="#">{{ $lang === 'bn' ? 'ক্যারিয়ার' : 'Careers' }}</a>
      </div>
      <div class="footer-col">
        <h4>{{ $lang === 'bn' ? 'সাপোর্ট' : 'Support' }}</h4>
        <a href="#">{{ $lang === 'bn' ? 'যোগাযোগ' : 'Contact' }}</a>
        <a href="#">{{ $lang === 'bn' ? 'ডকুমেন্টেশন' : 'Documentation' }}</a>
        <a href="#">{{ $lang === 'bn' ? 'প্রাইভেসি পলিসি' : 'Privacy Policy' }}</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; {{ date('Y') }} {{ $lang === 'bn' ? 'হিসাবিজ প্রপার্টি। সর্বস্বত্ব সংরক্ষিত।' : 'Hishabiz Property. All rights reserved.' }}</span>
      <span>{{ $lang === 'bn' ? 'বাংলাদেশে ❤️ দিয়ে তৈরি' : 'Made with ❤️ in Bangladesh' }}</span>
    </div>
  </div>
</footer>

</body>
</html>
