// Zero-Latency Audio Engine
let audioContext = null;
let soundBuffer = null;

function initAudioSystem() {
    if (window.TYPEFORGE_SETTINGS.sound === 'off') return;
    
    if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
        
        // Uses local files to prevent browser CORS blocking
        const soundUrls = {
            'click': 'click.mp3',
            'pop': 'pop.mp3',
            'beep': 'beep.mp3'
        };
        
        fetch(soundUrls[window.TYPEFORGE_SETTINGS.sound])
            .then(res => res.arrayBuffer())
            .then(buf => audioContext.decodeAudioData(buf))
            .then(decoded => { soundBuffer = decoded; })
            .catch(e => console.log('Audio file missing or blocked:', e));
    }
    // Resume context if browser suspended it
    if (audioContext.state === 'suspended') {
        audioContext.resume();
    }
}

function playKeySound() {
    if (window.TYPEFORGE_SETTINGS.sound === 'off' || !audioContext || !soundBuffer) return;
    const source = audioContext.createBufferSource();
    source.buffer = soundBuffer;
    source.connect(audioContext.destination);
    source.start(0);
}

document.addEventListener('keydown', initAudioSystem, { once: true });

const wordPools = {
    words: [
        "the", "be", "to", "of", "and", "a", "in", "that", "have", "i", 
        "it", "for", "not", "on", "with", "he", "as", "you", "do", "at", 
        "this", "but", "his", "by", "from", "they", "we", "say", "her", "she", 
        "or", "an", "will", "my", "one", "all", "would", "there", "their", "what", 
        "so", "up", "out", "if", "about", "who", "get", "which", "go", "me", 
        "when", "make", "can", "like", "time", "no", "just", "him", "know", "take", 
        "people", "into", "year", "your", "good", "some", "could", "them", "see", "other", 
        "than", "then", "now", "look", "only", "come", "its", "over", "think", "also", 
        "back", "after", "use", "two", "how", "our", "work", "first", "well", "way", 
        "even", "new", "want", "because", "any", "these", "give", "day", "most", "us", 
        "are", "is", "was", "were", "been", "being", "have", "has", "had", "do", 
        "does", "did", "shall", "should", "may", "might", "must", "can", "could", "will", 
        "would", "fast", "slow", "type", "keyboard", "screen", "code", "logic", "data", "web",
        "developer", "system", "design", "user", "interface", "app", "project", "test", "run", "play",
        "game", "score", "high", "low", "best", "worst", "train", "practice", "learn", "skill",
        "speed", "accuracy", "focus", "mind", "hand", "finger", "key", "press", "click", "sound",
        "music", "audio", "video", "image", "photo", "color", "black", "white", "red", "blue",
        "green", "yellow", "orange", "purple", "dark", "light", "sun", "moon", "star", "sky",
        "water", "fire", "earth", "wind", "tree", "flower", "grass", "animal", "dog", "cat",
        "bird", "fish", "food", "drink", "eat", "sleep", "walk", "run", "jump", "fly",
        "swim", "drive", "ride", "car", "bus", "train", "plane", "boat", "ship", "bike",
        "house", "home", "room", "door", "window", "bed", "chair", "table", "book", "pen",
        "paper", "pencil", "school", "class", "student", "teacher", "study", "read", "write", "math",
        "science", "history", "art", "music", "sport", "game", "team", "player", "win", "lose",
        "happy", "sad", "angry", "scared", "brave", "strong", "weak", "smart", "dumb", "funny"
    ],
    punctuations: ["test1", "42", "hello,", "world!", "2026", "code;", "100%", "true?", "data_base", "x=10;", "if(y)", "A/C", "$50", "version_3", "99.9%", "user#1"],
    pangrams: [
        "the quick brown fox jumps over the lazy dog",
        "pack my box with five dozen liquor jugs",
        "sphinx of black quartz judge my vow",
        "waltz bad nymph for quick jigs vex"
    ]
};

let currentCategory = 'words';

// DOM Elements
const textDisplay = document.getElementById("text-display");
const hiddenInput = document.getElementById("hidden-input");
const timerEl = document.getElementById("timer");
const wpmEl = document.getElementById("wpm");
const accuracyEl = document.getElementById("accuracy");
const restartBtn = document.getElementById("restart-btn");
const modeBtns = document.querySelectorAll(".mode-btn");
const resultsModal = document.getElementById("results-modal");
const modalRestartBtn = document.getElementById("modal-restart-btn");

// State Variables
let timeLimit = 60; 
let timeLeft = timeLimit;
let isDailyChallenge = false;
let timer = null;
let testStarted = false;
let totalTyped = 0;
let correctTyped = 0;
let currentPassage = "";

// Keyboard Heatmap Variables
let keyErrors = {};

function resetKeyErrors() {
    keyErrors = {};
    document.querySelectorAll('.kb-key').forEach(keyEl => {
        keyEl.classList.remove('error-low', 'error-high');
    });
}

function getDailyChallengePassage() {
    const today = new Date().toISOString().split('T')[0];
    let hash = 0;
    for (let i = 0; i < today.length; i++) {
        hash = today.charCodeAt(i) + ((hash << 5) - hash);
    }
    let a = hash;
    function random() {
      var t = a += 0x6D2B79F5;
      t = Math.imul(t ^ t >>> 15, t | 1);
      t ^= t + Math.imul(t ^ t >>> 7, t | 61);
      return ((t ^ t >>> 14) >>> 0) / 4294967296;
    }

    let pool = wordPools['words'];
    let selectedWords = [];
    for (let i = 0; i < 70; i++) {
        selectedWords.push(pool[Math.floor(random() * pool.length)]);
    }
    return selectedWords.join(' ');
}

function generatePassage() {
    if (isDailyChallenge) {
        return getDailyChallengePassage();
    }
    
    let wordPool = wordPools[currentCategory];

    if (window.TYPEFORGE_SETTINGS?.isTraining && window.TYPEFORGE_SETTINGS.weakKeys.length > 0) {
        const weakKeys = window.TYPEFORGE_SETTINGS.weakKeys;
        console.log("🎯 Training Mode Active! Targeting keys:", weakKeys);
        
        // STRICT FILTER: Keep words that contain AT LEAST ONE of the target weak keys
        const strictPool = wordPool.filter(word => {
            const lowerWord = word.toLowerCase();
            return weakKeys.some(key => lowerWord.includes(key));
        });
        
        console.log("📊 Words available after filtering:", strictPool.length);

        // Only fallback if the strict pool is basically empty (e.g., < 2 words)
        if (strictPool.length >= 2) {
            wordPool = strictPool;
        } else {
            console.warn("⚠️ Not enough words matched! Falling back to master list.");
        }
    } else {
        console.log("🟢 Standard Test Mode Active.");
    }

    let selectedWords = [];
    if (currentCategory === 'pangrams') {
        for (let i = 0; i < 4; i++) {
            selectedWords.push(wordPool[Math.floor(Math.random() * wordPool.length)]);
        }
        return selectedWords.join('. ') + '.';
    } else {
        for (let i = 0; i < 70; i++) {
            selectedWords.push(wordPool[Math.floor(Math.random() * wordPool.length)]);
        }
        return selectedWords.join(' ');
    }
}

function initTest() {
    clearInterval(timer);
    timeLeft = timeLimit;
    testStarted = false;
    totalTyped = 0;
    correctTyped = 0;
    
    resetKeyErrors();
    resultsModal.classList.add("hidden");
    
    // Reset modal title for Legend mode
    const modalHeader = document.querySelector('.modal-header h2');
    if(modalHeader) {
        modalHeader.textContent = "Test Complete"; 
        modalHeader.style.color = "#d1d0c5";
    }

    timerEl.textContent = `${timeLeft}s`;
    wpmEl.textContent = "0";
    accuracyEl.textContent = "100%";
    hiddenInput.value = "";
    hiddenInput.disabled = false;

    currentPassage = generatePassage(); 
    textDisplay.innerHTML = "";
    
    currentPassage.split("").forEach((char, index) => {
        const span = document.createElement("span");
        span.classList.add("char");
        if (index === 0) span.classList.add("current"); 
        span.textContent = char;
        textDisplay.appendChild(span);
    });

    focusInput();
}

modeBtns.forEach(btn => {
    if (btn.id === 'daily-challenge-btn') return;
    btn.addEventListener("click", () => {
        isDailyChallenge = false;
        modeBtns.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        timeLimit = parseInt(btn.getAttribute("data-time"));
        if (document.getElementById('daily-challenge-btn')) {
            document.getElementById('daily-challenge-btn').classList.remove("active");
        }
        initTest();
    });
});

const dailyChallengeBtn = document.getElementById('daily-challenge-btn');
if (dailyChallengeBtn) {
    dailyChallengeBtn.addEventListener("click", () => {
        isDailyChallenge = true;
        modeBtns.forEach(b => b.classList.remove("active"));
        dailyChallengeBtn.classList.add("active");
        timeLimit = 60; // Standardize daily challenge time to 60s
        initTest();
    });
}

textDisplay.addEventListener("click", focusInput);
function focusInput() {
    hiddenInput.focus();
}

function startTimer() {
    testStarted = true;
    timer = setInterval(() => {
        if (timeLeft > 0) {
            timeLeft--;
            timerEl.textContent = `${timeLeft}s`;
            calculateStats();
            
            // Chart variables removed
            
        } else {
            endTest();
        }
    }, 1000);
}

// getBaseKey removed to track exact characters

// --- Pro Mode Rule: Disable Backspace ---
hiddenInput.addEventListener("keydown", (e) => {
    // Get the current difficulty from the global settings object, default to beginner
    const difficulty = window.TYPEFORGE_SETTINGS?.difficulty || 'beginner';
    
    // If Pro mode is active and the user tries to backspace, block it
    if (difficulty === 'pro' && e.key === 'Backspace') {
        e.preventDefault(); // Completely stops the backspace action
    }
});

hiddenInput.addEventListener("input", () => {
    if (!testStarted) startTimer();

    const typedValue = hiddenInput.value;
    const charSpans = textDisplay.querySelectorAll(".char");
    
    totalTyped = typedValue.length;
    correctTyped = 0;

    charSpans.forEach((span, index) => {
        const typedChar = typedValue[index];
        span.classList.remove("correct", "incorrect", "current");

        if (typedChar == null) {
            if (index === typedValue.length) span.classList.add("current"); 
        } else if (typedChar === span.textContent) {
            span.classList.add("correct");
            correctTyped++;
            if (index === typedValue.length - 1) playKeySound();
        } else {
            span.classList.add("incorrect");
            const missedChar = span.textContent; // Keeps exact case/symbol (e.g., '!' or '1')
            keyErrors[missedChar] = (keyErrors[missedChar] || 0) + 1;
            
            if (window.TYPEFORGE_SETTINGS.difficulty === 'legend') {
                endTest(true); // Trigger legend failure
                return;
            }
        }
    });

    calculateStats();

    if (typedValue.length >= currentPassage.length) {
        endTest();
    }
});

function calculateStats() {
    const timeElapsed = timeLimit - timeLeft;
    const accuracy = totalTyped > 0 ? Math.round((correctTyped / totalTyped) * 100) : 100;
    accuracyEl.textContent = `${accuracy}%`;

    if (timeElapsed > 0) {
        const wpm = Math.round((correctTyped / 5) / (timeElapsed / 60));
        wpmEl.textContent = wpm >= 0 ? wpm : 0;
    }
}

function endTest(isLegendFail = false) {
    clearInterval(timer);
    hiddenInput.disabled = true;
    
    // Handle UI for failure
    const modalTitle = document.querySelector('.modal-header h2');
    if (isLegendFail) {
        timerEl.textContent = "Failed!";
        modalTitle.textContent = "Test Failed (Legend Mode)";
        modalTitle.style.color = "#ca4754";
    } else {
        timerEl.textContent = "Finished!";
        modalTitle.textContent = "Test Complete";
        modalTitle.style.color = "#d1d0c5";
    }
    
    const timeElapsed = timeLimit - timeLeft;
    const timeInMins = timeElapsed / 60;
    
    const rawWpm = timeInMins > 0 ? Math.round((totalTyped / 5) / timeInMins) : 0;
    const netWpm = timeInMins > 0 ? Math.round((correctTyped / 5) / timeInMins) : 0;
    const accuracy = totalTyped > 0 ? Math.round((correctTyped / totalTyped) * 100) : 100;
    const incorrect = totalTyped - correctTyped;
    
    document.getElementById("modal-net-wpm").textContent = netWpm;
    document.getElementById("modal-raw-wpm").textContent = rawWpm;
    document.getElementById("modal-accuracy").textContent = `${accuracy}%`;
    document.getElementById("modal-chars").textContent = `${correctTyped} / ${incorrect} / 0`;
    
    // Only save to database if it is NOT a legend fail AND it is NOT a training drill
    const isTrainingMode = window.TYPEFORGE_SETTINGS?.isTraining === true;
    
    if (!isLegendFail && !isTrainingMode) {
        let payload = { 
            net_wpm: netWpm, 
            raw_wpm: rawWpm, 
            accuracy: accuracy,
            difficulty_mode: window.TYPEFORGE_SETTINGS?.difficulty || 'beginner',
            key_errors: keyErrors 
        };
        
        if (typeof isDailyChallenge !== 'undefined' && isDailyChallenge) {
            payload.challenge_date = new Date().toISOString().split('T')[0];
        }
        
        fetch('save_score.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).catch(err => console.error(err));
    } else if (isTrainingMode) {
        console.log("🛠️ Training drill completed. Score will not be saved to global leaderboard.");
    }

    resultsModal.classList.remove("hidden");
    renderKeyHeatmap();
}

function renderKeyHeatmap() {
    // Clear all previous errors
    document.querySelectorAll('.kb-key, .kb-half').forEach(el => {
        el.classList.remove('error-low', 'error-high');
    });

    // Highlight full keys (letters, space, etc.)
    document.querySelectorAll('.kb-key:not(.split-key)').forEach(keyEl => {
        const char = keyEl.dataset.key;
        if(!char) return;
        const errors = keyErrors[char] || keyErrors[char.toUpperCase()] || 0;
        if (errors > 3) keyEl.classList.add('error-high');
        else if (errors > 0) keyEl.classList.add('error-low');
    });

    // Highlight split key halves independently
    document.querySelectorAll('.kb-half').forEach(halfEl => {
        const subChar = halfEl.dataset.subkey;
        if(!subChar) return;
        const errors = keyErrors[subChar] || 0;
        if (errors > 3) halfEl.classList.add('error-high');
        else if (errors > 0) halfEl.classList.add('error-low');
    });
}

restartBtn.addEventListener("click", initTest);
modalRestartBtn.addEventListener("click", initTest);

document.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !resultsModal.classList.contains("hidden")) {
        initTest();
    }
});

const customSwitch = document.getElementById('custom-switch-input');
const customPanel = document.getElementById('custom-panel');
const catBtns = document.querySelectorAll('.cat-btn');

if (customSwitch && customPanel) {
    customSwitch.addEventListener('change', () => {
        if (customSwitch.checked) {
            customPanel.classList.remove('hidden');
        } else {
            customPanel.classList.add('hidden');
            // Reset category back to default standard words and refresh
            currentCategory = 'words';
            catBtns.forEach(b => {
                b.classList.remove('active');
                b.style.color = '#646669';
                b.style.background = 'transparent';
            });
            const defaultWordBtn = document.querySelector('.cat-btn[data-category="words"]');
            if (defaultWordBtn) {
                defaultWordBtn.classList.add('active');
                defaultWordBtn.style.color = '#e2b714';
                defaultWordBtn.style.background = '#1e1e24';
            }
            initTest();
        }
    });
}

if (catBtns) {
    catBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            catBtns.forEach(b => {
                b.classList.remove('active');
                b.style.color = '#646669';
                b.style.background = 'transparent';
            });
            btn.classList.add('active');
            btn.style.color = '#e2b714';
            btn.style.background = '#1e1e24';
            currentCategory = btn.getAttribute('data-category');
            initTest();
        });
    });
}

initTest();
