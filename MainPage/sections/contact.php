<section class="contact scale-in" id="contact">
  <div class="container">
    <h2 class="section-title">Contact Us</h2>
    <p class="section-description">Get in touch with us for any inquiries or assistance.</p>
    
    <div class="contact-container">
      <div class="contact-form">
        <form action = "database/contact_us.php" method = "post" id="contactForm" >
          <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Your Name" required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Your Email" required>
          </div>
          <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" class="form-control" placeholder="Your Message" required></textarea>
          </div>
          <button type="submit" class="submit-btn">Send Message</button>
        </form>
      </div>
      
      <div class="contact-info">
        <div class="info-item">
          <div class="info-icon">
            <img src="images/location.svg" alt="">
          </div>
          <div class="info-text">
            <h3>Address</h3>
            <p>123 Medical Center Drive New York, NY 10001</p>
          </div>
        </div>
        
        <div class="info-item">
          <div class="info-icon">
            <img src="images/bluebigphone.svg" alt="">
          </div>
          <div class="info-text">
            <h3>Phone</h3>
            <p>+1 (555) 123-4567</p>
          </div>
        </div>
        
        <div class="info-item">
          <div class="info-icon">
            <img src="images/bluemessage.svg" alt="">
          </div>
          <div class="info-text">
            <h3>Email</h3>
            <p>info@medicare.com</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>