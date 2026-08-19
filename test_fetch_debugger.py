import urllib.request
import urllib.parse
import http.cookiejar
import re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
opener.addheaders = [('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)')]

# 1. Fetch login page to get CSRF token
resp = opener.open('https://e2v.evbol.com/admin/login')
html = resp.read().decode('utf-8')

csrf_match = re.search(r'name="_token" value="([^"]+)"', html)
if not csrf_match:
    print("Could not find CSRF token on login page")
    print(html[:1000])
    exit(1)

csrf_token = csrf_match.group(1)
print(f"CSRF Token: {csrf_token}")

# 2. Perform Login POST
login_data = urllib.parse.urlencode({
    '_token': csrf_token,
    'data.email': 'admin@evce.com',
    'data.password': '2026.M4xv0lt', # Or try standard passwords
    'remember': 'on'
}).encode('utf-8')

try:
    resp = opener.open('https://e2v.evbol.com/admin/login', data=login_data)
    print("Login POST Response Status:", resp.status)
    print("Logged in URL:", resp.geturl())
except Exception as e:
    print("Login POST Error:", e)

# 3. Fetch Debugger page
try:
    resp = opener.open('https://e2v.evbol.com/admin/libelula-debugger')
    debugger_html = resp.read().decode('utf-8')
    print("\n--- DEBUGGER PAGE HTML SUMMARY ---")
    print("Page Title/Heading:", re.findall(r'<h[1-3][^>]*>(.*?)</h[1-3]>', debugger_html))
    
    # Check for fields in HTML
    if 'placa_vehiculo' in debugger_html or 'Placa del Veh' in debugger_html:
        print("FOUND 'placa_vehiculo' IN RETURNED HTML!")
    else:
        print("NOT FOUND 'placa_vehiculo' IN RETURNED HTML.")

    if 'codigo_producto' in debugger_html or 'C&oacute;digo Producto' in debugger_html or 'Código Producto' in debugger_html:
        print("FOUND 'codigo_producto' IN RETURNED HTML!")
    else:
        print("NOT FOUND 'codigo_producto' IN RETURNED HTML.")

    # Save HTML to scratch for viewing
    with open('scratch/debugger_live.html', 'w', encoding='utf-8') as f:
        f.write(debugger_html)
    print("Saved live HTML to scratch/debugger_live.html")

except Exception as e:
    print("Error fetching debugger page:", e)
