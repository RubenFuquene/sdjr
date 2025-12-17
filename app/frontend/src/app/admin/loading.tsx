export default function AdminLoading() {
  return (
    <div className="bg-login-gradient flex min-h-screen items-center justify-center px-4 py-10 sm:py-16 md:py-20">
      <div className="w-full max-w-md rounded-2xl bg-white px-6 py-8 shadow-login-card sm:px-8 sm:py-10 animate-pulse" aria-busy="true">
        <div className="mb-6 flex flex-col items-center gap-3 text-center">
          <div className="h-20 w-20 rounded-full bg-[var(--color-border)]" />
          <div className="h-4 w-40 rounded bg-[var(--color-border)]" />
          <div className="h-3 w-28 rounded bg-[var(--color-border)]" />
        </div>
        <div className="flex flex-col gap-4">
          <div className="h-4 w-28 rounded bg-[var(--color-border)]" />
          <div className="h-11 w-full rounded-lg bg-[var(--color-border)]" />
          <div className="h-4 w-24 rounded bg-[var(--color-border)]" />
          <div className="h-11 w-full rounded-lg bg-[var(--color-border)]" />
          <div className="h-11 w-full rounded-full bg-[var(--color-border)]" />
        </div>
      </div>
    </div>
  );
}
