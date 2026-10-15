const axios = require("axios");

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function runCron() {
    try {
        let stop = false;
        while (!stop) {
            try {
                const response = await axios.get("https://partner.unizflow.com/api/v1/operations/internal/cron");
            } catch (error) {
                console.error("Error during request:", error.message);
            }
            await delay(500); // Wait for 0.5 seconds before the next iteration
        }
    } catch(e) {}
}

// Start the cron task
runCron();
