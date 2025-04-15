<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Thrifted Threads</title>
  <link rel="icon" href="images/logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="bg-[#EFDCAB] text-[#443627] min-h-screen flex flex-col">

  <!-- Donation Form Container -->
  <main class="max-w-3xl w-full mx-auto bg-white mt-14 p-10 rounded-xl shadow-lg flex-1">
    <h1 class="text-3xl font-extrabold text-center mb-2">Donate Your Preloved Items</h1>
    <p class="text-center text-sm text-[#443627]/70 mb-8">Support sustainable fashion by giving your gently used clothes, bags, or shoes a new life.</p>

    <form action="donate_process.php" method="POST" enctype="multipart/form-data" class="space-y-6">
      
      <!-- Full Name -->
      <div>
        <label for="name" class="block text-sm font-semibold mb-1">Full Name</label>
        <input type="text" id="name" name="full_name" placeholder="Juan Dela Cruz" required
               class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:ring-2 focus:ring-[#D98324] focus:outline-none transition">
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-semibold mb-1">Email</label>
        <input type="email" id="email" name="email" placeholder="you@email.com" required
               class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:ring-2 focus:ring-[#D98324] focus:outline-none transition">
      </div>

      <!-- Contact Number -->
      <div>
        <label for="contact" class="block text-sm font-semibold mb-1">Contact Number</label>
        <input type="text" id="contact" name="contact" maxlength="11" minlength="11" inputmode="numeric" required
               placeholder="09171234567"
               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
               class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:ring-2 focus:ring-[#D98324] focus:outline-none transition">
      </div>

      <!-- Item Description -->
      <div>
        <label for="items" class="block text-sm font-semibold mb-1">Item Description</label>
        <textarea id="items" name="item_description" rows="4" required
                  placeholder="Describe the item/s you want to donate..."
                  class="w-full px-4 py-2 border border-[#ccc] rounded-lg focus:ring-2 focus:ring-[#D98324] focus:outline-none transition resize-none"></textarea>
      </div>

      <!-- Upload Photo -->
      <div>
        <label for="photo" class="block text-sm font-semibold mb-1">Optional Photo</label>
        <input type="file" id="photo" name="photo"
               class="block w-full text-sm text-[#443627] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-medium file:bg-[#D98324] file:text-white hover:file:bg-[#443627] transition">
      </div>

      <!-- Donation Method -->
      <div>
        <label class="block text-sm font-semibold mb-2">Donation Method</label>
        <div class="flex items-center space-x-8">
          <label class="flex items-center space-x-2">
            <input type="radio" name="method" value="dropoff" class="accent-[#D98324]" checked>
            <span>Drop-off</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="radio" name="method" value="pickup" class="accent-[#D98324]">
            <span>Request Pickup</span>
          </label>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="text-center pt-4">
        <button type="submit"
                class="bg-[#D98324] hover:bg-[#443627] text-white font-semibold px-6 py-2 rounded-lg transition duration-200 ease-in-out">
          Submit Donation
        </button>
      </div>
    </form>

    <!-- Back to Home -->
    <div class="text-center mt-8">
      <a href="landingpage.php"
         class="inline-block bg-[#443627] hover:bg-[#D98324] text-white font-semibold px-6 py-2 rounded-lg transition duration-200 ease-in-out">
        ← Back to Home
      </a>
    </div>
  </main>

  <!-- Footer -->
  <footer class="text-center text-sm text-[#443627]/70 py-6">
    &copy; 2025 Thrifted Threads • Giving clothes a second chance<br>
    📞 Contact us: <a href="tel:09171234567" class="underline hover:text-[#D98324]">0917 123 4567</a>
  </footer>

</body>
</html>