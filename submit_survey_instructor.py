"""
Dedicated Survey Submitter for INSTRUCTORS (10% Disagree, 70% Agree, 20% Strongly Agree)
for DRIVEED HUB Survey.

Guarantees:
- Page 2 Consent: 100% strictly selects 'Yes, I agree to participate'
- Page 3 Role: Strictly 'Instructor' (Name left blank)
- Evaluation questions: 10% Disagree, 70% Agree, 20% Strongly Agree
- Feedback & Improvements: Left blank

Usage:
  python submit_survey_instructor.py                 # Submits 1 response
  python submit_survey_instructor.py -n 5            # Submits 5 responses
  python submit_survey_instructor.py -p -n 10        # Submits 10 responses spaced randomly per minute
"""

import argparse
import datetime
import random
import sys
import time
from playwright.sync_api import sync_playwright

FORM_URL = "https://docs.google.com/forms/d/e/1FAIpQLSd9u6KQ2wOcHt-aNPQVoSfc43VX2ypvmxD1ZufXpLqAlfUPMg/viewform"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

# Default ratio: 10% Disagree, 70% Agree, 20% Strongly Agree
DEFAULT_DISAGREE = 10
DEFAULT_AGREE = 70
DEFAULT_VERY_AGREE = 20

def submit_one(idx_label="1/1", weights=(DEFAULT_DISAGREE, DEFAULT_AGREE, DEFAULT_VERY_AGREE)):
    w_disagree, w_agree, w_very_agree = weights
    now_str = datetime.datetime.now().strftime("%H:%M:%S")
    print(f"[{now_str}] [{idx_label}] Starting submission (Role: Instructor | {w_disagree}% Disagree, {w_agree}% Agree, {w_very_agree}% Strongly Agree)...")
    start_t = time.time()

    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path=CHROME_PATH, headless=True)
        page = browser.new_page()

        # Load form with retry
        for attempt in range(3):
            try:
                page.goto(FORM_URL, timeout=30000)
                page.wait_for_load_state("networkidle")
                break
            except Exception as e:
                if attempt == 2:
                    browser.close()
                    raise e
                time.sleep(2)

        for step in range(1, 16):
            time.sleep(0.5)

            # 1. Handle Consent (Strictly target 'Yes, I agree to participate')
            consent = page.query_selector('div[role="radio"][data-value="Yes, I agree to participate"]')
            if consent:
                consent.click()
                time.sleep(0.2)

            # 2. Handle Role dropdown (Strictly select 'Instructor')
            dropdown = page.query_selector('div[role="listbox"]')
            if dropdown:
                dropdown.click()
                time.sleep(0.3)
                instructor_opt = page.query_selector('div[role="option"][data-value="Instructor"], div[role="option"]:has-text("Instructor")')
                if instructor_opt:
                    instructor_opt.click()
                time.sleep(0.3)

            # 3. Handle Likert Questions with 10% Disagree / 70% Agree / 20% Strongly Agree
            radio_groups = page.query_selector_all('div[role="radiogroup"]')
            for rg in radio_groups:
                rg_radios = rg.query_selector_all('div[role="radio"]')
                disagree_r = None
                agree_r = None
                strongly_agree_r = None

                for r in rg_radios:
                    val = (r.get_attribute('data-value') or '').strip().lower()
                    if val == 'disagree':
                        disagree_r = r
                    elif val == 'agree':
                        agree_r = r
                    elif val == 'strongly agree':
                        strongly_agree_r = r

                candidates = []
                c_weights = []

                if disagree_r and w_disagree > 0:
                    candidates.append(disagree_r)
                    c_weights.append(w_disagree)
                if agree_r and w_agree > 0:
                    candidates.append(agree_r)
                    c_weights.append(w_agree)
                if strongly_agree_r and w_very_agree > 0:
                    candidates.append(strongly_agree_r)
                    c_weights.append(w_very_agree)

                if candidates:
                    chosen = random.choices(candidates, weights=c_weights, k=1)[0]
                    chosen.click()
                    time.sleep(0.05)

            # 4. Check for Submit button (Overall Feedback page - left blank)
            submit_btn = page.query_selector('//span[text()="Isumite" or text()="Submit"]')
            if submit_btn:
                submit_btn.click()
                time.sleep(2.5)
                confirm = page.inner_text('body')
                elapsed = round(time.time() - start_t, 1)
                if 'recorded' in confirm or 'tugon' in confirm:
                    print(f"[{datetime.datetime.now().strftime('%H:%M:%S')}] [{idx_label}] SUCCESS: Recorded in {elapsed}s.")
                    browser.close()
                    return True
                browser.close()
                return False

            # 5. Check for Next button
            next_btn = page.query_selector('//span[text()="Susunod" or text()="Next"]')
            if next_btn:
                next_btn.click()
                time.sleep(0.5)
            else:
                break

        browser.close()
        return False

def run_paced(total_target, min_rate=1, max_rate=3, interval_seconds=60, weights=(DEFAULT_DISAGREE, DEFAULT_AGREE, DEFAULT_VERY_AGREE)):
    w_disagree, w_agree, w_very_agree = weights
    print("=== Starting Paced Interval Mode (Instructor) ===")
    print(f"Total target: {total_target if total_target else 'Infinite (until stopped)'}")
    print(f"Rate: {min_rate} to {max_rate} submissions per {interval_seconds}s interval")
    print(f"Weights: {w_disagree}% Disagree, {w_agree}% Agree, {w_very_agree}% Strongly Agree\n")

    completed = 0
    minute = 1

    try:
        while True:
            if total_target and completed >= total_target:
                break

            remaining = (total_target - completed) if total_target else max_rate
            count_this_block = random.randint(min_rate, max_rate)
            count_this_block = min(count_this_block, remaining)

            block_start = time.time()
            now_str = datetime.datetime.now().strftime("%H:%M:%S")
            print(f"--- [Minute {minute}] [{now_str}] Scheduled: {count_this_block} response(s) ---")

            for i in range(1, count_this_block + 1):
                idx_label = f"{completed + 1}/{total_target}" if total_target else f"#{completed + 1}"
                ok = submit_one(idx_label, weights=weights)
                if ok:
                    completed += 1

                if i < count_this_block:
                    pause = random.uniform(5.0, 15.0)
                    print(f"  -> Waiting {pause:.1f}s before next submission in this block...")
                    time.sleep(pause)

            elapsed_block = time.time() - block_start
            time_left = interval_seconds - elapsed_block
            if time_left > 0 and (not total_target or completed < total_target):
                print(f"--- [Minute {minute} Done] Resting {time_left:.1f}s until next minute... ---\n")
                time.sleep(time_left)
            else:
                print(f"--- [Minute {minute} Done] Completed in {elapsed_block:.1f}s. ---\n")

            minute += 1

    except KeyboardInterrupt:
        print("\nProcess interrupted by user.")

    print(f"\nAll done! Successfully submitted {completed} response(s) across {minute - 1} minute(s).")

def run_sequential(count, delay=1.0, weights=(DEFAULT_DISAGREE, DEFAULT_AGREE, DEFAULT_VERY_AGREE)):
    w_disagree, w_agree, w_very_agree = weights
    print(f"Starting sequential submission of {count} response(s) (Role: Instructor)...")
    print(f"Distribution: {w_disagree}% Disagree / {w_agree}% Agree / {w_very_agree}% Strongly Agree\n")
    successes = 0
    for i in range(1, count + 1):
        try:
            if submit_one(f"{i}/{count}", weights=weights):
                successes += 1
        except Exception as e:
            print(f"[{i}/{count}] Error: {e}")
        if i < count:
            time.sleep(delay)
    print(f"\nAll done! {successes}/{count} responses submitted.")

def main():
    parser = argparse.ArgumentParser(description="DRIVEED HUB Survey Submitter for INSTRUCTORS (10/70/20).")
    parser.add_argument("-n", "--count", type=int, default=1, help="Total responses to submit (default: 1)")
    parser.add_argument("-p", "--paced", action="store_true", help="Enable paced interval mode (random per minute)")
    parser.add_argument("--min-rate", type=int, default=1, help="Min submissions per interval (default: 1)")
    parser.add_argument("--max-rate", type=int, default=3, help="Max submissions per interval (default: 3)")
    parser.add_argument("--interval", type=int, default=60, help="Interval window in seconds (default: 60)")
    parser.add_argument("-d", "--delay", type=float, default=1.0, help="Delay for sequential mode (default: 1.0s)")
    parser.add_argument("--disagree", type=int, default=DEFAULT_DISAGREE, help="Weight for Disagree (default: 10)")
    parser.add_argument("--agree", type=int, default=DEFAULT_AGREE, help="Weight for Agree (default: 70)")
    parser.add_argument("--very-agree", type=int, default=DEFAULT_VERY_AGREE, help="Weight for Strongly Agree (default: 20)")
    args = parser.parse_args()

    weights = (args.disagree, args.agree, args.very_agree)

    if args.paced:
        run_paced(
            total_target=args.count,
            min_rate=args.min_rate,
            max_rate=args.max_rate,
            interval_seconds=args.interval,
            weights=weights
        )
    else:
        run_sequential(args.count, args.delay, weights=weights)

if __name__ == "__main__":
    main()
