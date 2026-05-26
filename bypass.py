import re
import requests
from Crypto.Cipher import AES

url = "https://ekjholokekolkata.page.gd/central_notifier.php"
headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"}

session = requests.Session()

try:
    # 1. Fetch the raw page content
    response = session.get(url, headers=headers).text

    # 2. BULLETPROOF SEARCH: Find any string of alphanumeric characters that looks like the tokens
    # This ignores layout structures, quotation formats, and functions completely.
    matches = re.findall(r'[a-f0-9]{32}', response)
    
    if len(matches) >= 3:
        # Extract the key elements safely
        a_hex = str(matches).strip()
        b_hex = str(matches).strip()
        c_hex = str(matches).strip()

        # Safely convert to bytes now that we know they are 100% clean strings
        key = bytes.fromhex(a_hex)
        iv = bytes.fromhex(b_hex)
        ciphertext = bytes.fromhex(c_hex)

        # 3. Decrypt the security token using standard AES-128-CBC
        cipher = AES.new(key, AES.MODE_CBC, iv)
        decrypted = cipher.decrypt(ciphertext)
        
        # Clean trailing validation layout spaces
        clean_bytes = decrypted.rstrip(b'\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f')
        test_cookie_value = clean_bytes.hex()

        # 4. Inject the token securely into the session cookie jar
        session.cookies.set("__test", test_cookie_value, domain="ekjholokekolkata.page.gd", path="/")
        
        # 5. Connect directly to your database processing logic
        final_response = session.get(url, headers=headers)
        output = final_response.text.strip()
        
        # Check if we bypassed the wall successfully
        if "script" not in output:
            print("Success! Passed firewall challenge and triggered the notifier script.")
        else:
            print(f"Triggered, but firewall is asking for redirect: {output[:100]}")
    else:
        # Fallback if the firewall is already open
        if "script" not in response.lower():
            print("No challenge found. Script executed directly.")
        else:
            print(f"Failed to find all 3 validation strings. Found: {len(matches)}")

except Exception as e:
    print(f"Bypass execution failed: {str(e)}")
