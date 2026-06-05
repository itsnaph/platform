<?php
// FILE: pages/how_it_works.php
require_once '../includes/auth.php';
$pageTitle = 'How It Works';
include '../includes/header.php';
?>
<main>
  <!-- Hero -->
  <div class="hero">
    <div class="container">
      <h1 class="fw-bold mb-3">How HustleHub Works</h1>
      <p class="lead" style="opacity:0.85;max-width:600px;margin:0 auto">
        A safe, escrow-protected marketplace connecting South African township workers with local clients.
      </p>
    </div>
  </div>

  <div class="container py-5">

    <!-- FOR CLIENTS -->
    <h2 class="fw-bold text-center mb-4" style="color:var(--primary)">For Clients</h2>
    <div class="row g-4 mb-5">
      <?php
      $steps = [
        ['1','Register','Create a free client account and verify your email with our 6-digit OTP.','bi-person-plus'],
        ['2','Browse Services','Search and filter local worker listings by category and price range.','bi-search'],
        ['3','Book &amp; Pay','Pick a date and pay securely via PayFast. Your money is held in escrow — not paid to the worker yet.','bi-credit-card'],
        ['4','Job Done?','When the worker finishes, you confirm completion. Escrow is released to the worker only after your confirmation.','bi-check-circle'],
        ['5','Leave a Review','Rate the worker out of 5 stars. Reviews help the community pick the best people.','bi-star'],
        ['6','Dispute? No Problem','If something goes wrong, raise a dispute. Our admin team reviews both sides and decides where the money goes.','bi-shield-check'],
      ];
      foreach ($steps as [$num,$title,$desc,$icon]): ?>
        <div class="col-12 col-md-4">
          <div class="card h-100 border-0 shadow-sm p-3 text-center">
            <div style="font-size:2.5rem;color:var(--accent)" class="mb-2"><i class="bi <?= $icon ?>"></i></div>
            <div class="fw-bold small mb-1" style="color:var(--primary)">Step <?= $num ?> — <?= $title ?></div>
            <p class="text-muted small mb-0"><?= $desc ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- FOR WORKERS -->
    <h2 class="fw-bold text-center mb-4" style="color:var(--primary)">For Workers</h2>
    <div class="row g-4 mb-5">
      <?php
      $wsteps = [
        ['1','Register as Worker','Sign up, verify your email, and create your worker profile.','bi-briefcase'],
        ['2','List Your Service','Create a service listing with a title, description, price, and category. Submit for admin approval.','bi-plus-circle'],
        ['3','Accept Bookings','When a client books your service, you get notified. Accept and mark the job as started.','bi-calendar-check'],
        ['4','Do Great Work','Complete the job. The client confirms completion — which releases your payment from escrow.','bi-tools'],
        ['5','Get Paid','Your earnings are released after client confirmation or admin resolution. Build your rating over time.','bi-cash-coin'],
      ];
      foreach ($wsteps as [$num,$title,$desc,$icon]): ?>
        <div class="col-12 col-md-4">
          <div class="card h-100 border-0 shadow-sm p-3 text-center">
            <div style="font-size:2.5rem;color:var(--primary)" class="mb-2"><i class="bi <?= $icon ?>"></i></div>
            <div class="fw-bold small mb-1" style="color:var(--primary)">Step <?= $num ?> — <?= $title ?></div>
            <p class="text-muted small mb-0"><?= $desc ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Escrow Explainer -->
    <div class="escrow-box p-4 text-center mb-5" style="border-radius:16px;max-width:700px;margin:0 auto">
      <h4 class="fw-bold mb-2">What is Escrow?</h4>
      <p class="mb-0">
        When you pay for a service on HustleHub, your money is held in a secure escrow — like a safe middleman.
        The worker cannot receive it until you confirm the job is done. If there's a dispute, our admin team
        decides. This protects both you and the worker from unfair outcomes.
      </p>
    </div>

    <!-- FAQ -->
    <h2 class="fw-bold text-center mb-4" style="color:var(--primary)">Frequently Asked Questions</h2>
    <div class="accordion mb-5" id="faqAccordion" style="max-width:700px;margin:0 auto">
      <?php
      $faqs = [
        ['Is HustleHub free to use?','Yes. Registering and browsing is completely free. Workers list their services for free.'],
        ['What payments does HustleHub accept?','We use PayFast, which supports Visa, Mastercard, Instant EFT, and Capitec Pay — all popular South African payment methods.'],
        ['What if the worker does not show up?','Raise a dispute from your dashboard. Our admin team will review the case and can refund your money if the worker did not deliver.'],
        ['Can I book more than one service?','Yes. You can have multiple active bookings at the same time.'],
        ['How do I become a worker?','During registration, select "Worker". Once verified, you can create service listings immediately. They go live after admin approval.'],
      ];
      foreach ($faqs as $i => [$q,$a]): ?>
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button <?= $i>0?'collapsed':'' ?>" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
              <?= $q ?>
            </button>
          </h2>
          <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i===0?'show':'' ?>" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted small"><?= $a ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- CTA -->
    <div class="text-center py-4">
      <a href="register.php" class="btn btn-primary btn-lg me-2">Get Started</a>
      <a href="browse.php" class="btn btn-outline-secondary btn-lg">Browse Services</a>
    </div>

  </div>
</main>
<?php include '../includes/footer.php'; ?>
