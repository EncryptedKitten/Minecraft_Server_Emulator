import glob, configparser, os

def fix(x):
	print(x)
	f = open(x)
	fcon = f.read()
	f.close()

	fcon = fcon.replace("/path/to/sites/mcse", "/path/to/sites/mcse")
	f = open(x, "w")
	f.write(fcon)
	f.close()

def main():
	
	files = glob.glob("**/*", recursive=True)

	for x in files:
		if os.path.isfile(x):
			fix(x)


if __name__ == "__main__":
	main()