import glob, configparser, os

old = "1.0.0.127.in-addr.arpa"
new = "example.com"

def fix(x):
	print(x)
	f = open(x)
	fcon = f.read()
	f.close()

	fcon = fcon.replace("email." + old, "email." + new)
	fcon = fcon.replace(old, new)

	f = open(x, "w")
	f.write(fcon)
	f.close()

def main():
	
	files = glob.glob("**/*", recursive=True)

	for x in files:
		if os.path.isfile(x) and not x.endswith(".py"):
			fix(x)


if __name__ == "__main__":
	main()