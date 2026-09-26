/**
 * VoiceAnnouncer - Modul Call Room / Roll Call Atletik
 * Menggunakan Web Speech API + Web Audio API (untuk efek bel/chime)
 * 
 * @author System Generated
 * @version 1.0
 */
class VoiceAnnouncer {
    constructor(options = {}) {
        this.lang = options.lang || 'id-ID';
        this.rate = options.rate || 0.85;
        this.pitch = options.pitch || 1.0;
        this.volume = options.volume || 1.0;
        this.delayBetweenAthletes = options.delay || 2500;
        this.isSpeaking = false;
        this.isPaused = false;
        this.queue = [];
        this.currentUtterance = null;
        this.onStatusChange = options.onStatusChange || null;
        this.onTextHighlight = options.onTextHighlight || null;
        this.audioContext = null;
        this.aborted = false;
    }

    /**
     * Cek apakah browser mendukung Web Speech API
     */
    static isSupported() {
        return 'speechSynthesis' in window;
    }

    /**
     * Dapatkan daftar voice yang tersedia
     */
    getVoices() {
        return new Promise((resolve) => {
            let voices = speechSynthesis.getVoices();
            if (voices.length > 0) {
                resolve(voices);
                return;
            }
            speechSynthesis.onvoiceschanged = () => {
                resolve(speechSynthesis.getVoices());
            };
            // Fallback timeout
            setTimeout(() => resolve(speechSynthesis.getVoices()), 500);
        });
    }

    /**
     * Cari voice terbaik untuk bahasa Indonesia
     */
    async getBestVoice() {
        const voices = await this.getVoices();
        // Prioritas: id-ID > ms-MY (mirip) > en-US (fallback)
        let best = voices.find(v => v.lang === 'id-ID');
        if (!best) best = voices.find(v => v.lang.startsWith('id'));
        if (!best) best = voices.find(v => v.lang === 'ms-MY');
        if (!best) best = voices.find(v => v.lang.startsWith('ms'));
        if (!best) best = voices.find(v => v.lang.startsWith('en'));
        return best || voices[0] || null;
    }

    /**
     * Inisialisasi Web Audio API untuk efek suara
     */
    initAudio() {
        if (!this.audioContext) {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }
        return this.audioContext;
    }

    /**
     * Bunyikan bel/chime sebelum pengumuman
     * @param {string} type - 'call' | 'warning' | 'final'
     */
    playChime(type = 'call') {
        return new Promise((resolve) => {
            try {
                const ctx = this.initAudio();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);

                const now = ctx.currentTime;
                gain.gain.setValueAtTime(0, now);

                if (type === 'call') {
                    // Ding-dong: dua nada naik
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(523.25, now);      // C5
                    gain.gain.linearRampToValueAtTime(0.4, now + 0.05);
                    osc.frequency.setValueAtTime(659.25, now + 0.4); // E5
                    gain.gain.setValueAtTime(0.4, now + 0.4);
                    osc.frequency.setValueAtTime(783.99, now + 0.8); // G5
                    gain.gain.linearRampToValueAtTime(0, now + 1.3);
                    osc.start(now);
                    osc.stop(now + 1.3);
                    setTimeout(resolve, 1400);
                } else if (type === 'warning') {
                    // Dua bel peringatan
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(880, now);
                    gain.gain.linearRampToValueAtTime(0.5, now + 0.05);
                    gain.gain.linearRampToValueAtTime(0, now + 0.3);
                    gain.gain.linearRampToValueAtTime(0.5, now + 0.5);
                    gain.gain.linearRampToValueAtTime(0, now + 0.8);
                    osc.start(now);
                    osc.stop(now + 0.9);
                    setTimeout(resolve, 1000);
                } else if (type === 'final') {
                    // Tiga bel urgensi
                    osc.type = 'square';
                    osc.frequency.setValueAtTime(1046.5, now);
                    gain.gain.setValueAtTime(0, now);
                    for (let i = 0; i < 3; i++) {
                        const t = now + i * 0.35;
                        gain.gain.linearRampToValueAtTime(0.3, t + 0.02);
                        gain.gain.linearRampToValueAtTime(0, t + 0.2);
                    }
                    osc.start(now);
                    osc.stop(now + 1.2);
                    setTimeout(resolve, 1300);
                } else {
                    resolve();
                }
            } catch (e) {
                console.warn('Audio chime error:', e);
                resolve();
            }
        });
    }

    /**
     * Ucapkan satu kalimat
     * @returns {Promise}
     */
    speak(text) {
        return new Promise(async (resolve, reject) => {
            if (this.aborted) { reject('aborted'); return; }
            if (!VoiceAnnouncer.isSupported()) { resolve(); return; }

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = this.lang;
            utterance.rate = this.rate;
            utterance.pitch = this.pitch;
            utterance.volume = this.volume;

            const voice = await this.getBestVoice();
            if (voice) utterance.voice = voice;

            this.currentUtterance = utterance;
            this.isSpeaking = true;

            if (this.onTextHighlight) {
                this.onTextHighlight(text);
            }

            utterance.onend = () => {
                this.isSpeaking = false;
                this.currentUtterance = null;
                resolve();
            };
            utterance.onerror = (e) => {
                this.isSpeaking = false;
                this.currentUtterance = null;
                if (e.error === 'canceled' || e.error === 'interrupted') {
                    reject('aborted');
                } else {
                    resolve(); // Continue even on minor errors
                }
            };

            speechSynthesis.speak(utterance);
        });
    }

    /**
     * Delay helper
     */
    delay(ms) {
        return new Promise((resolve, reject) => {
            const timer = setTimeout(() => {
                if (this.aborted) reject('aborted');
                else resolve();
            }, ms);
            // Store timer for potential cancellation
            this._currentTimer = timer;
        });
    }

    /**
     * Update status
     */
    setStatus(text, type = 'info') {
        if (this.onStatusChange) {
            this.onStatusChange(text, type);
        }
    }

    /**
     * Nama tingkat panggilan
     */
    getCallLevelName(level) {
        switch (level) {
            case 1: return 'Pertama';
            case 2: return 'Kedua';
            case 3: return 'Terakhir';
            default: return 'Pertama';
        }
    }

    getChimeType(level) {
        switch (level) {
            case 1: return 'call';
            case 2: return 'warning';
            case 3: return 'final';
            default: return 'call';
        }
    }

    /**
     * Panggil seluruh peserta dalam 1 heat
     */
    async announceHeat(eventData, heatNumber, pesertaList, callLevel = 1) {
        this.aborted = false;
        const chimeType = this.getChimeType(callLevel);
        const callName = this.getCallLevelName(callLevel);

        try {
            // 1. Chime pembuka
            this.setStatus('🔔 Membunyikan bel...', 'info');
            await this.playChime(chimeType);
            await this.delay(500);

            // 2. Pembukaan
            this.setStatus(`📢 Panggilan ${callName}...`, 'speaking');
            await this.speak(`Perhatian. Perhatian.`);
            await this.delay(800);

            const panggilan = callLevel === 3 
                ? `Ini adalah panggilan terakhir. Final call.` 
                : `Panggilan ${callName}.`;
            await this.speak(panggilan);
            await this.delay(600);

            // 3. Info acara
            const namaAcara = eventData.nama_acara || 'Lomba Atletik';
            const nomorAcara = eventData.nomor_acara || '';
            const babak = eventData.babak || 'Final';

            this.setStatus(`📋 Nomor acara ${nomorAcara}: ${namaAcara}`, 'speaking');
            await this.speak(`Nomor acara ${nomorAcara}. ${namaAcara}. Babak ${babak}. Heat ${heatNumber}.`);
            await this.delay(1000);

            // 4. Panggil setiap peserta
            const kategori = (eventData.kategori_lomba || '').toLowerCase();
            const isLapangan = ['lompat', 'lempar', 'tolak'].some(k => kategori.includes(k));

            for (let i = 0; i < pesertaList.length; i++) {
                if (this.aborted) throw 'aborted';

                const p = pesertaList[i];
                const nama = p.nama_atlet || p.nama || 'Atlet';
                const noDada = p.no_dada || '';
                const sekolah = p.nama_sekolah || p.sekolah || '';
                const lane = p.lane || p.lintasan || (i + 1);

                const posisi = isLapangan ? `Nomor urut ${lane}` : `Lintasan ${lane}`;

                this.setStatus(`🏃 ${posisi} — ${nama} (${noDada})`, 'speaking');
                
                let kalimat = `${posisi}. Nomor dada ${noDada}. ${nama}.`;
                if (sekolah) kalimat += ` Dari ${sekolah}.`;

                await this.speak(kalimat);
                
                if (i < pesertaList.length - 1) {
                    await this.delay(this.delayBetweenAthletes);
                }
            }

            // 5. Penutup
            await this.delay(800);
            const tujuan = isLapangan 
                ? 'segera menuju area lapangan yang ditentukan' 
                : 'segera menuju garis start';
            
            this.setStatus('📣 Penutup pengumuman...', 'speaking');
            await this.speak(`Seluruh peserta Heat ${heatNumber} dimohon ${tujuan}. Terima kasih.`);
            await this.delay(500);

            // 6. Chime penutup
            await this.playChime('call');

            this.setStatus(`✅ Panggilan ${callName} Heat ${heatNumber} selesai.`, 'done');
        } catch (e) {
            if (e === 'aborted') {
                this.setStatus('⏹️ Panggilan dihentikan.', 'stopped');
            } else {
                console.error('Announce error:', e);
                this.setStatus('❌ Terjadi error saat panggilan.', 'error');
            }
        }
    }

    /**
     * Panggil 1 atlet individu
     */
    async announceSingle(atletData, eventData = {}) {
        this.aborted = false;
        try {
            await this.playChime('call');
            await this.delay(400);

            const nama = atletData.nama || atletData.nama_atlet || 'Atlet';
            const noDada = atletData.no_dada || '';
            const sekolah = atletData.sekolah || atletData.nama_sekolah || '';
            const lane = atletData.lane || '';

            let kalimat = `Perhatian.`;
            if (lane) kalimat += ` Lintasan ${lane}.`;
            kalimat += ` Nomor dada ${noDada}. ${nama}.`;
            if (sekolah) kalimat += ` Dari ${sekolah}.`;
            kalimat += ` Dimohon segera bersiap.`;

            this.setStatus(`🏃 Memanggil: ${nama} (${noDada})`, 'speaking');
            await this.speak(kalimat);
            
            this.setStatus(`✅ Selesai memanggil ${nama}.`, 'done');
        } catch (e) {
            if (e !== 'aborted') {
                console.error('Single announce error:', e);
            }
        }
    }

    /**
     * Stop semua ucapan
     */
    stop() {
        this.aborted = true;
        this.isSpeaking = false;
        this.isPaused = false;
        speechSynthesis.cancel();
        if (this._currentTimer) clearTimeout(this._currentTimer);
        this.setStatus('⏹️ Panggilan dihentikan.', 'stopped');
    }

    /**
     * Pause
     */
    pause() {
        if (speechSynthesis.speaking && !speechSynthesis.paused) {
            speechSynthesis.pause();
            this.isPaused = true;
            this.setStatus('⏸️ Dijeda...', 'paused');
        }
    }

    /**
     * Resume
     */
    resume() {
        if (speechSynthesis.paused) {
            speechSynthesis.resume();
            this.isPaused = false;
            this.setStatus('▶️ Melanjutkan...', 'speaking');
        }
    }
}

// Export untuk penggunaan global
window.VoiceAnnouncer = VoiceAnnouncer;
