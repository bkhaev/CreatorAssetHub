
// Synchronious Time Delay
// 1000: 1 second
function sleep(milliseconds) {
    const date = Date.now();
    let currentDate = null;
    do {
        currentDate = Date.now();
    } while (currentDate - date < milliseconds);
}

function sleepAsync(milliseconds) {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            resolve();
        }, milliseconds);
    });
}

export const [testSleep, testSleepAsync] = [sleep, sleepAsync];