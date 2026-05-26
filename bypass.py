import re
import requests

url = "https://ekjholokekolkata.page.gd/central_notifier.php"

headers = {
    "User-Agent": "Mozilla/5.0"
}

session = requests.Session()

def fetch_page():
    try:
        response = session.get(url, headers=headers, timeout=20)
        return response.text
    except Exception as e:
        print(f"Request failed: {e}")
        return None


def extract_hex_tokens(html):
    # Find 32-char hex strings
    return re.findall(r'\b[a-f0-9]{32}\b', html.lower())


def main():
    print("Starting cron fetch...")

    html = fetch_page()
    if not html:
        return

    tokens = extract_hex_tokens(html)

    print(f"Found {len(tokens)} hex tokens")

    # Just debug output (NO bypass logic)
    if tokens:
        print("Sample tokens:")
        for t in tokens[:5]:
            print(" -", t)

    # Detect redirect script presence
    if "slowAES" in html or "location.href" in html:
        print("Warning: page contains redirect / AES gate script")
    else:
        print("Page looks clean (no gate detected)")

    print("Done.")


if __name__ == "__main__":
    main()
