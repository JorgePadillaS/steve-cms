import base64
import os
import subprocess
import sys

def sync_file(local_path, remote_path):
    print(f"Syncing {local_path} to {remote_path}...")
    if not os.path.exists(local_path):
        print(f"Error: {local_path} not found")
        return

    with open(local_path, 'rb') as f:
        content = f.read()
    
    b64_content = base64.b64encode(content).decode('utf-8')
    chunk_size = 1000
    chunks = [b64_content[i:i+chunk_size] for i in range(0, len(b64_content), chunk_size)]
    
    # Reset file
    subprocess.run(['gcloud', 'compute', 'ssh', 'evseserver', '--zone', 'us-central1-a', '--project', 'active-display-484301-j7', '--command', "echo -n '' > /tmp/temp.b64"], check=True)
    
    for i, chunk in enumerate(chunks):
        print(f"  Sending chunk {i+1}/{len(chunks)}...")
        subprocess.run(['gcloud', 'compute', 'ssh', 'evseserver', '--zone', 'us-central1-a', '--project', 'active-display-484301-j7', '--command', f"printf '{chunk}' >> /tmp/temp.b64"], check=True)
    
    # Decode and move
    print("  Finalizing...")
    subprocess.run(['gcloud', 'compute', 'ssh', 'evseserver', '--zone', 'us-central1-a', '--project', 'active-display-484301-j7', '--command', f"base64 -d /tmp/temp.b64 > /tmp/temp.php && sudo docker cp /tmp/temp.php steve-cms-app:{remote_path}"], check=True)
    print("  Done.")

if __name__ == "__main__":
    files_to_sync = [
        ('app/Http/Controllers/Api/V1/Mobile/WalletController.php', '/app/app/Http/Controllers/Api/V1/Mobile/WalletController.php'),
        ('app/Http/Controllers/Api/V1/Mobile/ChargingSessionController.php', '/app/app/Http/Controllers/Api/V1/Mobile/ChargingSessionController.php'),
        ('routes/api.php', '/app/routes/api.php'),
        ('resources/views/pdf/wallet_history.blade.php', '/app/resources/views/pdf/wallet_history.blade.php')
    ]
    
    for local, remote in files_to_sync:
        try:
            sync_file(local, remote)
        except Exception as e:
            print(f"Failed to sync {local}: {e}")
