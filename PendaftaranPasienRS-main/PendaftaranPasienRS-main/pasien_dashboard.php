<?php
session_start();
if ($_SESSION['role'] != 'pasien') {
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html lang="id">
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pasien | RS Sehat Selalu</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 2s infinite',
                        'fade-in': 'fadeIn 0.8s ease-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'shimmer': 'shimmer 2s linear infinite',
                        'heartbeat': 'heartbeat 1.5s ease-in-out infinite',
                        'wave': 'wave 2s ease-in-out infinite',
                        'rotate-slow': 'rotate 20s linear infinite',
                        'scale-pulse': 'scalePulse 2s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 20px rgba(79, 172, 254, 0.5)' },
                            '100%': { boxShadow: '0 0 30px rgba(79, 172, 254, 0.8), 0 0 40px rgba(79, 172, 254, 0.3)' },
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition: '200% 0' },
                        },
                        heartbeat: {
                            '0%, 100%': { transform: 'scale(1)' },
                            '50%': { transform: 'scale(1.05)' },
                        },
                        wave: {
                            '0%, 100%': { transform: 'rotate(0deg)' },
                            '25%': { transform: 'rotate(5deg)' },
                            '75%': { transform: 'rotate(-5deg)' },
                        },
                        scalePulse: {
                            '0%, 100%': { transform: 'scale(1)' },
                            '50%': { transform: 'scale(1.02)' },
                        }
                    },
                    backgroundImage: {
                        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                        'gradient-conic': 'conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))',
                        'shimmer': 'linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent)',
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
        }

        /* Custom focus styles */
        .custom-focus:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.3);
        }

        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Enhanced button hover effect */
        .btn-hover-effect {
            position: relative;
            overflow: hidden;
        }

        .btn-hover-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-hover-effect:hover::before {
            left: 100%;
        }

        /* Floating animation for particles */
        .floating-particle {
            animation: float 4s ease-in-out infinite;
        }

        .floating-particle:nth-child(2n) {
            animation-delay: -2s;
        }

        .floating-particle:nth-child(3n) {
            animation-delay: -1s;
        }

        /* Medical cross animation */
        .medical-cross {
            animation: heartbeat 2s ease-in-out infinite;
        }

        /* Pulse ring effect */
        .pulse-ring {
            position: relative;
        }

        .pulse-ring::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            border: 2px solid #4facfe;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: pulse 2s infinite;
        }

        /* Gradient text effect */
        .gradient-text {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Shimmer loading effect */
        .shimmer-bg {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        /* Chatbot specific styles */
        .chatbot-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 999;
            transition: all 0.3s ease;
        }

        .chatbot-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        .chatbot-box {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            max-height: 500px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
            z-index: 1000;
            overflow: hidden;
        }

        .bot, .user {
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 80%;
        }

        .bot {
            background: #f8f9fa;
            color: #2c3e50;
            margin-right: auto;
        }

        .user {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            margin-left: auto;
            text-align: right;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 via-purple-600 to-cyan-500 min-h-screen font-sans overflow-x-hidden">

    <!-- Floating decorative elements -->
    <div class="fixed inset-0 pointer-events-none z-10">
        <div class="absolute top-20 left-10 w-4 h-4 bg-blue-400 rounded-full opacity-60 floating-particle"></div>
        <div class="absolute top-40 right-20 w-6 h-6 bg-cyan-400 rounded-full opacity-40 floating-particle"></div>
        <div class="absolute bottom-32 left-20 w-3 h-3 bg-purple-400 rounded-full opacity-70 floating-particle"></div>
        <div class="absolute bottom-20 right-10 w-5 h-5 bg-blue-300 rounded-full opacity-50 floating-particle"></div>
        <div class="absolute top-1/2 left-5 w-2 h-2 bg-cyan-300 rounded-full opacity-80 floating-particle"></div>
        <div class="absolute top-1/3 right-5 w-4 h-4 bg-purple-300 rounded-full opacity-60 floating-particle"></div>
    </div>

    <div class="min-h-screen p-4 relative z-20">
        <!-- Welcome Card -->
        <div class="glass rounded-3xl shadow-2xl p-8 mb-8 text-center transform hover:scale-105 transition-all duration-500 animate-fade-in">
            <h1 class="text-5xl font-bold text-white mb-4 drop-shadow-lg">
                <i class="bi bi-heart-pulse mr-4 medical-cross"></i>Selamat Datang!
            </h1>
            <p class="text-xl text-white opacity-90 animate-slide-up drop-shadow-md">
                Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>! Apa yang bisa kami bantu hari ini?
            </p>
            
            <!-- Animated health indicators -->
            <div class="mt-6 flex justify-center space-x-3">
                <div class="pulse-ring w-3 h-3 bg-green-400 rounded-full"></div>
                <div class="pulse-ring w-3 h-3 bg-blue-400 rounded-full" style="animation-delay: 0.5s;"></div>
                <div class="pulse-ring w-3 h-3 bg-cyan-400 rounded-full" style="animation-delay: 1s;"></div>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Booking Card -->
            <a href="pasien_booking.php" class="group">
                <div class="glass rounded-2xl shadow-xl p-8 text-center transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-blue-400">
                    <i class="bi bi-calendar-check text-6xl text-blue-400 mb-6 group-hover:animate-bounce block"></i>
                    <h3 class="text-2xl font-bold text-white mb-4 drop-shadow-lg">Booking Janji</h3>
                    <p class="text-white opacity-80">Buat janji temu dengan dokter spesialis</p>
                </div>
            </a>

            <!-- History Card -->
            <a href="pasien_riwayat.php" class="group">
                <div class="glass rounded-2xl shadow-xl p-8 text-center transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-green-400" style="animation-delay: 0.1s;">
                    <i class="bi bi-clock-history text-6xl text-green-400 mb-6 group-hover:animate-bounce block"></i>
                    <h3 class="text-2xl font-bold text-white mb-4 drop-shadow-lg">Riwayat Booking</h3>
                    <p class="text-white opacity-80">Lihat riwayat janji temu Anda</p>
                </div>
            </a>

            <!-- Logout Card -->
            <a href="logout.php" class="group">
                <div class="glass rounded-2xl shadow-xl p-8 text-center transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-red-400" style="animation-delay: 0.2s;">
                    <i class="bi bi-box-arrow-right text-6xl text-red-400 mb-6 group-hover:animate-bounce block"></i>
                    <h3 class="text-2xl font-bold text-white mb-4 drop-shadow-lg">Logout</h3>
                    <p class="text-white opacity-80">Keluar dari sistem</p>
                </div>
            </a>

            <!-- WhatsApp Card -->
            <a href="https://wa.me/6281220812483?text=Halo%20Admin%20RS%20Sehat%20Selalu,%20saya%20ingin%20bertanya." 
               target="_blank" 
               rel="noopener noreferrer"
               class="group">
                <div class="glass rounded-2xl shadow-xl p-8 text-center transform hover:scale-105 hover:-translate-y-2 transition-all duration-500 animate-slide-up border-l-4 border-green-500" style="animation-delay: 0.3s;">
                    <i class="bi bi-whatsapp text-6xl text-green-500 mb-6 group-hover:animate-bounce block"></i>
                    <h3 class="text-2xl font-bold text-white mb-4 drop-shadow-lg">Customer Service</h3>
                    <p class="text-white opacity-80">Hubungi kami via WhatsApp</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Chatbot Widget -->
    <button class="chatbot-toggle animate-pulse" onclick="toggleChatbot()">
        <i class="bi bi-chat-dots"></i>
    </button>

    <div class="chatbot-box" id="chatbot">
        <div class="bg-gradient-to-r from-blue-500 to-cyan-500 text-white p-4 font-semibold text-center text-lg">
            <i class="bi bi-robot mr-2"></i> Bantuan RS Sehat Selalu
        </div>
        <div class="p-4 h-80 overflow-y-auto" id="chatbotMessages">
            <div class="bot">Halo! Ada yang bisa kami bantu? 😊</div>
        </div>
        <div class="flex border-t border-gray-200 p-4">
            <input type="text" id="chatInput" placeholder="Ketik pertanyaan Anda..." 
                   class="flex-1 px-4 py-2 border-2 border-gray-200 rounded-full outline-none focus:border-blue-400 transition-colors duration-300 mr-3"
                   onkeypress="if(event.keyCode==13) sendMessage()">
            <button onclick="sendMessage()" 
                    class="px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-full hover:from-blue-600 hover:to-cyan-600 transition-all duration-300 transform hover:scale-105">
                <i class="bi bi-send"></i>
            </button>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- 3D Particles -->
    <script src="particles.js"></script>

    <script>
    function toggleChatbot() {
        const bot = document.getElementById('chatbot');
        bot.style.display = bot.style.display === 'flex' ? 'none' : 'flex';
    }

    function sendMessage() {
        const input = document.getElementById('chatInput');
        const msg = input.value.trim();
        if (!msg) return;

        const chatBox = document.getElementById('chatbotMessages');
        const userMsg = `<div class="user">${msg}</div>`;
        chatBox.innerHTML += userMsg;

        let response = getBotReply(msg);
        const botMsg = `<div class="bot">${response}</div>`;
        chatBox.innerHTML += botMsg;

        chatBox.scrollTop = chatBox.scrollHeight;
        input.value = '';
    }

    function getBotReply(message) {
        const lower = message.toLowerCase();
        if (lower.includes("jam buka") || lower.includes("buka")) {
            return "🏥 Kami buka setiap hari pukul 08.00 - 20.00 WIB.";
        } else if (lower.includes("daftar") || lower.includes("pendaftaran")) {
            return "📝 Silakan klik menu 'Booking Janji' untuk mendaftar konsultasi dengan dokter.";
        } else if (lower.includes("alamat")) {
            return "📍 Kami beralamat di Jl. Sehat No. 123, Jakarta Pusat.";
        } else if (lower.includes("jadwal dokter") || lower.includes("dokter")) {
            return "👨‍⚕️ Jadwal dokter:\n• Anak: Senin-Jumat 08.00-17.00\n• THT: Rabu 13.00-15.30\n• Gigi: Kamis 14.00-18.00\n• Penyakit Dalam: Selasa 10.00-14.00";
        } else if (lower.includes("biaya") || lower.includes("harga")) {
            return "💰 Biaya konsultasi bervariasi tergantung spesialisasi dokter. Silakan hubungi admin untuk informasi detail.";
        } else if (lower.includes("darurat") || lower.includes("emergency")) {
            return "🚨 Untuk keadaan darurat, silakan hubungi 119 atau datang langsung ke IGD kami.";
        } else {
            return "Maaf, pertanyaan Anda belum bisa kami jawab. Silakan hubungi admin via WhatsApp untuk bantuan lebih lanjut. 😊";
        }
    }
    </script>

</body>
</html>
