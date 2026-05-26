import re
import requests
from Crypto.Cipher import AES

url = "https://ekjholokekolkata.page.gd/central_notifier.php"
headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"}

session = requests.Session()

try:
    # 1. Fetch the raw page content
    response = session.get(url, headers=headers).text

    # 2. Extract every single valid hex sequence inside toNumbers("...")
    matches = re.findall(r'toNumbers\("([a-f0-9]+)"\)', response)
    
    if len(matches) >= 3:
        a_hex = str(matches)  # Key
        b_hex = str(matches)  # IV
        c_hex = str(matches)  # Ciphertext

        key = bytes.fromhex(a_hex)
        iv = bytes.fromhex(b_hex)
        ciphertext = bytes.fromhex(c_hex)

        # 3. Decrypt the security token using standard AES-128-CBC
        cipher = AES.new(key, AES.MODE_CBC, iv)
        decrypted = cipher.decrypt(ciphertext)
        
        # CLEAN FIX: Strip standard PKCS7/zero padding characters so it's clean hex
        # InfinityFree needs the pure hex calculation without trailing layout blocks
        clean_bytes = decrypted.rstrip(b'\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f')
        test_cookie_value = clean_bytes.hex()

        # 4. Inject the token securely into the live host domain container
        session.cookies.set("__test", test_cookie_value, domain="ekjholokekolkata.page.gd", path="/")
        
        # 5. Connect directly to your database processing logic
        final_response = session.get(url, headers=headers)
        output = final_response.text.strip()
        
        # Print the direct server outcome to the GitHub Action log window
        if "Status: Active" in output or "completed" in output.lower():
            print("Success! Your notifier backend script ran and processed changes.")
        else:
            print(f"Server triggered! Page output snippet: {output[:120]}")
    else:
        # Fallback if the firewall is temporarily resting
        print(f"No firewall challenge matched. Page start snippet: {response[:150]}")

except Exception as e:
    print(f"Bypass execution failed: {str(e)}")
