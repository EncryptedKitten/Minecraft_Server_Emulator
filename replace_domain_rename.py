import glob, configparser, os

old = "1.0.0.127.in-addr.arpa"
new = "example.com"

def main():
	
	changed = True
	while changed:
		files = glob.glob("**/*", recursive=True)
		changed = False
		for x in files:
			print(x)
			if old in os.path.basename(x):
				os.rename(x, x.replace(old, new))
				changed = True
				break

if __name__ == "__main__":
	main()