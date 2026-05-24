// টোস্ট নোটিফিকেশন দেখানোর ফাংশন
function showToast(message) {
  // আগের কোনো টোস্ট থাকলে রিমুভ করা
  const existingToast = document.querySelector(".toast-notification");
  if (existingToast) {
    existingToast.remove();
  }

  // নতুন টোস্ট তৈরি করা
  const toast = document.createElement("div");
  toast.className = "toast-notification";
  toast.innerText = message;
  document.body.appendChild(toast);

  // টোস্ট দেখানো
  setTimeout(() => {
    toast.classList.add("show");
  }, 100);

  // ৩ সেকেন্ড পর টোস্ট হাইড করা
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => {
      toast.remove();
    }, 300);
  }, 3000);
}

// 1. Text Copy Function (Bug Fixed for Hidden Elements)
function copyText(elementId, copyType) {
  const textElement = document.getElementById(elementId);

  if (textElement) {
    // hidden (display: none) থাকলে innerText কাজ করে না, তাই textContent নেওয়া হলো
    const textToCopy = textElement.textContent || textElement.innerText;

    // আধুনিক Clipboard API ব্যবহার করা
    navigator.clipboard
      .writeText(textToCopy.trim())
      .then(() => {
        showToast(`${copyType} কপি হয়েছে! ✅`);
      })
      .catch((err) => {
        console.error("কপি করতে সমস্যা হয়েছে: ", err);
        showToast("কপি ব্যর্থ হয়েছে ❌");
      });
  }
}

// 2. Direct Image Copy Function (আপডেটেড Fetch API ভার্সন)
async function copyImageToClipboard(imageUrl) {
  try {
    // ১. ছবিটির ডেটা ফেচ (Fetch) করে নিয়ে আসা
    const response = await fetch(imageUrl);
    if (!response.ok) {
      throw new Error("Network response was not ok");
    }
    const blob = await response.blob();

    // ২. ছবিটিকে Canvas-এর মাধ্যমে PNG তে কনভার্ট করা (কারণ ক্লিপবোর্ড শুধু PNG সাপোর্ট করে)
    const bitmap = await createImageBitmap(blob);
    const canvas = document.createElement("canvas");
    canvas.width = bitmap.width;
    canvas.height = bitmap.height;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(bitmap, 0, 0);

    // ৩. PNG ফাইল হিসেবে ক্লিপবোর্ডে সেভ করা
    canvas.toBlob(async (pngBlob) => {
      try {
        const item = new ClipboardItem({ "image/png": pngBlob });
        await navigator.clipboard.write([item]);
        showToast("ছবি কপি হয়েছে! মেসেন্জারে পেস্ট (Ctrl+V) করুন। 📸✅");
      } catch (err) {
        console.error("Clipboard write error:", err);
        showToast("আপনার ব্রাউজার ডাইরেক্ট ইমেজ কপি সাপোর্ট করছে না। ❌");
      }
    }, "image/png");
  } catch (err) {
    console.error("Image load error:", err);
    showToast("ছবি লোড করতে সমস্যা হয়েছে। পাথ চেক করুন। ❌");
  }
}
// 3. Download All Images Function (ব্রাউজার ব্লক বাইপাস করা)
async function downloadAllImages(urlsString, watchName) {
  if (!urlsString) {
    showToast("ডাউনলোড করার মতো কোনো ছবি নেই! ❌");
    return;
  }

  const urls = urlsString.split(",");
  showToast("সবগুলো ছবি ডাউনলোড হচ্ছে... দয়া করে অপেক্ষা করুন 📥");

  // লুপ চালিয়ে প্রতিটি ছবি ফেচ এবং ডাউনলোড করা
  for (let i = 0; i < urls.length; i++) {
    try {
      // Fetch API দিয়ে ছবি নিয়ে আসা
      const response = await fetch(urls[i]);
      if (!response.ok) throw new Error("Network error");
      const blob = await response.blob();

      // ফাইলের নাম সেট করা
      const fileName = `${watchName.replace(/\s+/g, "_")}_Image_${i + 1}.jpg`;
      const objectUrl = window.URL.createObjectURL(blob);

      // ডাউনলোড লিংক তৈরি
      const link = document.createElement("a");
      link.href = objectUrl;
      link.download = fileName;

      // লিংকটি বডিতে যোগ করে ক্লিক করা এবং রিমুভ করা
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      // অবজেক্ট URL রিলিজ করা (মেমরি ফাঁকা করার জন্য)
      setTimeout(() => window.URL.revokeObjectURL(objectUrl), 1000);

      // ব্রাউজার যেন মাল্টিপল ফাইল ব্লক না করে, তাই ৮০০ মিলি-সেকেন্ড গ্যাপ দেওয়া
      await new Promise((resolve) => setTimeout(resolve, 800));
    } catch (error) {
      console.error(`Error downloading image ${i + 1}:`, error);
    }
  }

  // ডাউনলোড শেষ হলে কনফার্মেশন দেখানো
  setTimeout(() => {
    showToast(`সবগুলো (${urls.length} টি) ছবি ডাউনলোড সম্পন্ন হয়েছে! ✅`);
  }, 1000);
}
