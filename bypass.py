import re
import requests
from Crypto.Cipher import AES

url = "https://ekjholokekolkata.page.gd/central_notifier.php"
headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"}

session = requests.Session()

try:
    # 1. Get the initial challenge page
    response = session.get(url, headers=headers).text

    # 2. Extract all strings inside toNumbers("...")
    matches = re.findall(r'toNumbers\("([a-f0-9]+)"\)', response)
    
    if len(matches) >= 3:
        a_hex = matches  # Key
        b_hex = matches  # IV (Initialization Vector)
        c_hex = matches  # Encrypted Ciphertext

        key = bytes.fromhex(a_hex)
        iv = bytes.fromhex(b_hex)
        ciphertext = bytes.fromhex(c_hex)

        # 3. Decrypt using AES-128-CBC mode
        cipher = AES.new(key, AES.MODE_CBC, iv)
        decrypted = cipher.decrypt(ciphertext)
        
        # Convert decrypted bytes to a clean hex string for the cookie
        test_cookie_value = decrypted.hex()

        # 4. Set the __test cookie into the session matching the host domain
        session.cookies.set("__test", test_cookie_value, domain="ekjholokekolkata.page.gd", path="/")
        
        # 5. Fire the actual PHP script request with the solved cookie attached
        final_response = session.get(url, headers=headers)
        print(f"Success! Server Output: {final_response.text.strip()}")
    else:
        # If the page doesn't contain the challenge, we might already have direct access
        print(f"No challenge found. Response snippet: {response[:150]}")

except Exception as e:
    print(f"Bypass execution failed: {str(e)}")
