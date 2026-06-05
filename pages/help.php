<?php
require_once '../includes/auth.php';
$pageTitle = 'Help & FAQ';
include '../includes/header.php';
?>
<main>

  <div class="hero">
    <div class="container">
      <h1 class="fw-bold mb-3">Help &amp; Support</h1>
      <p class="hero-sub">Answers to common questions about using HustleHub.</p>
    </div>
  </div>

  <div class="container py-5" style="max-width:760px">

    <h2 class="fw-bold mb-4" style="color:var(--primary)">Frequently Asked Questions</h2>

    <div class="accordion mb-5" id="helpAccordion">

      <?php
      $faqs = [
        ['For Clients','bi-person','#ffd',
          [
            ['How do I book a service?',
             'Browse services, open a listing, choose a date and click <strong>Book Now</strong>. You will be taken to PayFast to pay. Your money is held in escrow until you confirm the job is done.'],
            ['When does the worker get paid?',
             'Only after you confirm the job is complete on your client dashboard. If you raise a dispute, an admin decides where the money goes.'],
            ['What if the worker does not show up?',
             'Go to your booking on the client dashboard and click <strong>Raise Dispute</strong>. Our admin team will review the case and can issue a full refund.'],
            ['Can I cancel a booking?',
             'Contact the worker first. If the job has not started and both parties agree, an admin can cancel the booking and refund the escrow.'],
            ['What payment methods are accepted?',
             'HustleHub uses PayFast, which supports Visa, Mastercard, Instant EFT, and Capitec Pay.'],
          ]
        ],
        ['For Workers','bi-briefcase','#eef',
          [
            ['How do I create a listing?',
             'Log in as a worker, go to your dashboard and click <strong>Create New Listing</strong>. Fill in your title, description, category, price, and optionally upload a photo. Your listing goes live after admin approval.'],
            ['Why is my listing still pending?',
             'All new listings and edits are reviewed by an admin before going live. This usually takes less than 24 hours.'],
            ['How do I mark a job as started?',
             'Open the booking from your worker dashboard and click <strong>Start Job</strong>. This notifies the client that work has begun.'],
            ['When will I receive payment?',
             'After the client confirms the job is complete, escrow is released. You will see the status update on your dashboard.'],
          ]
        ],
        ['Account & Security','bi-shield-lock','#efe',
          [
            ['I forgot my password — what do I do?',
             'Use the <strong>Forgot Password</strong> link on the login page. A reset link will be sent to your registered email.'],
            ['How do I change my email or password?',
             'Go to <strong>My Account</strong> after logging in. You can update your email and password there.'],
            ['Is my payment information stored on HustleHub?',
             'No. All payment data is handled entirely by PayFast. HustleHub never stores card numbers or banking details.'],
          ]
        ],
      ];

      $idx = 0;
      foreach ($faqs as [$section, $icon, $bg, $items]): ?>
        <h5 class="fw-bold mt-4 mb-2" style="color:var(--primary)">
          <i class="bi <?= $icon ?> me-2"></i><?= $section ?>
        </h5>
        <?php foreach ($items as [$q, $a]): $id = 'h' . $idx++; ?>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button"
                      data-bs-toggle="collapse" data-bs-target="#<?= $id ?>">
                <?= $q ?>
              </button>
            </h2>
            <div id="<?= $id ?>" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
              <div class="accordion-body text-muted"><?= $a ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm p-4 text-center">
      <h5 class="fw-bold mb-2" style="color:var(--primary)">Still need help?</h5>
      <p class="text-muted mb-3">Email us and we will get back to you within 24 hours.</p>
      <a href="mailto:support@hustlehub.co.za" class="btn btn-primary">
        <i class="bi bi-envelope me-2"></i>support@hustlehub.co.za
      </a>
    </div>

  </div>
</main>
<?php include '../includes/footer.php'; ?>
