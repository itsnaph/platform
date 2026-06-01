// FILE: assets/js/app.js
// HustleHub - jQuery client-side logic

$(document).ready(function () {

  //1. Category filter pills - show/hide cards by category without page reload
  $('#category-filters').on('click', '.btn-filter', function () {
    $('.btn-filter').removeClass('active');
    $(this).addClass('active');
    const cat = $(this).data('cat');

    $('.service-card').each(function () {
      const match = cat === 'all' || $(this).data('cat') === cat;
      $(this).toggle(match);
    });
    updateResultCount();
  });

  //2. Price range slider - hide cards above the selected max price
  $('#price-range').on('input', function () {
    const max = parseInt($(this).val());
    $('#price-display').text('R0 – R' + max.toLocaleString());

    const activeCat = $('.btn-filter.active').data('cat') || 'all';
    $('.service-card').each(function () {
      const priceOk = parseInt($(this).data('price')) <= max;
      const catOk   = activeCat === 'all' || $(this).data('cat') === activeCat;
      $(this).toggle(priceOk && catOk);
    });
    updateResultCount();
  });

  function updateResultCount() {
    const visible = $('.service-card:visible').length;
    $('#results-count').text(visible + ' service' + (visible !== 1 ? 's' : '') + ' found');
  }

  //3. Text search - filter cards by title or category
  $('#search-input').on('input', function () {
    const q = $(this).val().toLowerCase().trim();
    if (!q) { $('.service-card').show(); updateResultCount(); return; }
    $('.service-card').each(function () {
      const title = $(this).find('.card-title').text().toLowerCase();
      const cat   = $(this).data('cat');
      $(this).toggle(title.includes(q) || cat.includes(q));
    });
    updateResultCount();
  });

  //4. Client confirms job is complete - sends AJAX to update booking status
  $(document).on('click', '.btn-confirm-complete', function () {
    const $btn      = $(this);
    const bookingId = $btn.data('booking-id');

    if (!confirm('Confirm the job is complete? This will release payment to the worker.')) return;

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing…');

    $.ajax({
      url:  '../pages/update_booking_status.php',
      type: 'POST',
      data: {
        booking_id:  bookingId,
        new_status:  'completed',
        csrf_token:  $('meta[name=csrf-token]').attr('content')
      },
      success: function (res) {
        try {
          const data = (typeof res === 'string') ? JSON.parse(res) : res;
          if (data.success) {
            window.location.reload();
          } else {
            alert('Error: ' + (data.message || 'Unknown error'));
            $btn.prop('disabled', false).text('Confirm Complete');
          }
        } catch (e) {
          alert('Unexpected response. Please refresh.');
          $btn.prop('disabled', false).text('Confirm Complete');
        }
      },
      error: function () {
        alert('Network error. Please try again.');
        $btn.prop('disabled', false).text('Confirm Complete');
      }
    });
  });

  //5. Worker accepts a booking
  $(document).on('click', '.btn-accept-booking', function () {
    const $btn      = $(this);
    const bookingId = $btn.data('booking-id');

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Accepting…');

    $.ajax({
      url:  '../pages/update_booking_status.php',
      type: 'POST',
      data: {
        booking_id: bookingId,
        new_status: 'confirmed',
        csrf_token: $('meta[name=csrf-token]').attr('content')
      },
      success: function (res) {
        const data = (typeof res === 'string') ? JSON.parse(res) : res;
        if (data.success) {
          window.location.reload();
        }
      }
    });
  });

  //6. Worker marks job as started
  $(document).on('click', '.btn-start-job', function () {
    const $btn      = $(this);
    const bookingId = $btn.data('booking-id');

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>…');

    $.ajax({
      url:  '../pages/update_booking_status.php',
      type: 'POST',
      data: { booking_id: bookingId, new_status: 'in_progress',
              csrf_token: $('meta[name=csrf-token]').attr('content') },
      success: function (res) {
        const data = (typeof res === 'string') ? JSON.parse(res) : res;
        if (data.success) {
          window.location.reload();
        }
      }
    });
  });

  //7. Character counter for review text box
  function updateCharCount() {
    const len = $('#review-text').val().length;
    $('#char-count').text(len + '/500');
    if (len > 450) {
      $('#char-count').css('color', '#dc3545');
    } else if (len > 350) {
      $('#char-count').css('color', 'var(--accent)');
    } else {
      $('#char-count').css('color', 'var(--text-muted)');
    }
  }
  if ($('#review-text').length) {
    $('#review-text').on('input', updateCharCount);
    updateCharCount(); //initialise on load
  }

  //8. Star rating picker - colour stars yellow when selected
  function applyStarColors(selectedVal) {
    $('#star-picker .star-label').each(function (i) {
      $(this).css('color', i < selectedVal ? '#f59e0b' : '#dee2e6');
    });
  }
  // Colour stars when a radio changes (triggered by label click)
  $('#star-picker input[type=radio]').on('change', function () {
    applyStarColors(parseInt($(this).val()));
  });
  // Initialise colours if a rating is already checked (page reload after error)
  var $checkedStar = $('#star-picker input[type=radio]:checked');
  if ($checkedStar.length) {
    applyStarColors(parseInt($checkedStar.val()));
  }

  //9. Show image preview before upload
  $('#service-image').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file (jpg, png, webp).');
      return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
      $('#img-preview').attr('src', e.target.result).removeClass('d-none');
      $('#img-preview-wrap').removeClass('d-none');
    };
    reader.readAsDataURL(file);
  });

  //10. Mobile sidebar toggle for admin panel
  $('#sidebar-toggle').on('click', function () {
    $('.admin-sidebar').toggleClass('open');
  });

  //11. Auto-dismiss alert messages after 4 seconds
  setTimeout(function () {
    $('.alert-dismissible').fadeOut(400, function () { $(this).remove(); });
  }, 4000);

  //12. Confirm before dangerous actions like delete or reject
  $(document).on('click', '.btn-danger-confirm', function (e) {
    if (!confirm($(this).data('confirm') || 'Are you sure?')) {
      e.preventDefault();
    }
  });

});
