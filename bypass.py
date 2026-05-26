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
    print(f"Debug: Found {len(matches)} validation elements on the page.")
    
    if len(matches) >= 3:
        # FORCE CASTING TO STRING: Explicitly convert list positions to clean strings
        a_hex = str(matches)  # Key
        b_hex = str(matches)  # IV
        c_hex = str(matches)  # Ciphertext

        # Safely convert strings to byte blocks
        key = bytes.fromhex(a_hex)
        iv = bytes.fromhex(b_hex)
        ciphertext = bytes.fromhex(c_hex)

        # 3. Decrypt the security token using standard AES-128-CBC
        cipher = AES.new(key, AES.MODE_CBC, iv)
        decrypted = cipher.decrypt(ciphertext)
        
        # Pull the clean hex validation output string
        test_cookie_value = str(decrypted.hex())

        # 4. Inject the token securely into the live host domain container
        session.cookies.set("__test", test_cookie_value, domain="ekjholokekolkata.page.gd", path="/")
        
        # 5. Connect directly to your database processing logic
        final_response = session.get(url, headers=headers)
        
        output = final_response.text.strip()
        if "Status: Active" in output:
            print("Success! Your notifier backend script ran and processed changes.")
        else:
            print(f"Server executed but returned a different layout: {output[:100]}")
    else:
        # Fallback tracking if the server doesn't serve the security page
        print(f"No firewall challenge matched. Page start snippet: {response[:150]}")

except Exception as e:
    print(f"Bypass execution failed: {str(e)}")
